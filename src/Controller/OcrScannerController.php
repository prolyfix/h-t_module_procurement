<?php

namespace Prolyfix\ProcurementBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\CrmBundle\Entity\ThirdParty;
use Prolyfix\HolidayAndTime\Entity\Media;
use Prolyfix\ProcurementBundle\Entity\DeliverySlip;
use Prolyfix\ProcurementBundle\Entity\DeliverySlipLine;
use Prolyfix\ProcurementBundle\Entity\Invoice;
use Prolyfix\ProcurementBundle\Entity\Order;
use Prolyfix\ProcurementBundle\Form\ParserType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/procurement/ocr', name: 'procurement_ocr_')]
class OcrScannerController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public static function getEntityFqcn(): string
    {
        // This controller is action-oriented (OCR workflow) but EasyAdmin requires an entity FQCN.
        return Media::class;
    }

    public function servePdf(int $id): Response
    {
        $media = $this->em->getRepository(Media::class)->find($id);

        if ($media === null) {
            throw $this->createNotFoundException('Media not found');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/private/medias/' . $media->getFileName();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }

        return new Response(
            file_get_contents($filePath),
            Response::HTTP_OK,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]
        );
    }

    public function scanner(): Response
    {
        $media = new Media();
        $form = $this->createForm(ParserType::class, $media);

        return $this->render('@ProlyfixProcurement/ocr/scanner.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $media = new Media();
        $form = $this->createForm(ParserType::class, $media);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse(['error' => 'Invalid form submission'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($media);
        $this->em->flush();

        $filePath = $this->getParameter('kernel.project_dir') . '/private/medias/' . $media->getFileName();

        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'File not found after upload'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $mistralApiKey = $this->getParameter('mistral_api_key');

        try {
            $result = $this->analyzeWithMistral($filePath, $mistralApiKey);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'AI analysis failed: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $result['mediaId'] = $media->getId();
        $result['fileName'] = $media->getFileName();

        $thirdParties = $this->em->getRepository(ThirdParty::class)->findAll();
        $result['knownThirdParties'] = array_map(fn(ThirdParty $tp) => [
            'id'   => $tp->getId(),
            'name' => $tp->getName(),
        ], $thirdParties);

        $result['relatedOrders'] = [];
        if (!empty($result['relatedDocumentId'])) {
            $orders = $this->em->getRepository(Order::class)->findBy(['orderId' => $result['relatedDocumentId']]);
            $result['relatedOrders'] = array_map(fn(Order $o) => [
                'id'      => $o->getId(),
                'orderId' => $o->getOrderId(),
            ], $orders);
        }

        return new JsonResponse($result);
    }

    public function submit(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonResponse(['error' => 'No data provided'], Response::HTTP_BAD_REQUEST);
        }

        $documentType = $data['documentType'] ?? null;

        $thirdParty = null;
        if (!empty($data['thirdPartyId'])) {
            $thirdParty = $this->em->getRepository(ThirdParty::class)->find($data['thirdPartyId']);
        }

        if ($thirdParty === null && !empty($data['newThirdPartyName'])) {
            $thirdParty = new ThirdParty();
            $thirdParty->setName($data['newThirdPartyName']);
            $this->em->persist($thirdParty);
        }

        $lines = $data['lines'] ?? [];

        switch ($documentType) {
            case 'delivery':
                $entity = new DeliverySlip();
                $entity->setDeliverySlipId($data['documentId'] ?? null);
                $entity->setDeliveryDate(!empty($data['documentDate']) ? new \DateTime($data['documentDate']) : null);
                $entity->setThirdParty($thirdParty);

                if (!empty($data['relatedOrderId'])) {
                    $order = $this->em->getRepository(Order::class)->find($data['relatedOrderId']);
                    if ($order !== null) {
                        $entity->setProcurementOrder($order);
                    }
                }

                if (!empty($data['relatedInvoiceId'])) {
                    $invoice = $this->em->getRepository(Invoice::class)->find($data['relatedInvoiceId']);
                    if ($invoice !== null) {
                        $entity->setInvoice($invoice);
                    }
                }

                foreach ($lines as $lineData) {
                    $line = new DeliverySlipLine();
                    $line->setDescription($lineData['description'] ?? null);
                    $line->setQuantity($lineData['quantity'] ?? null);
                    $line->setMeasure($lineData['measure'] ?? null);
                    $line->setGrossPrice($lineData['grossPrice'] ?? null);
                    $line->setNetPrice($lineData['netPrice'] ?? null);
                    $line->setVat($lineData['vat'] ?? null);
                    $entity->addDeliverySlipLine($line);
                }

                $this->em->persist($entity);
                break;

            case 'invoice':
                $entity = new Invoice();
                if ($thirdParty !== null) {
                    // Invoice does not yet have a direct thirdParty field; set via related order if available
                }
                if (!empty($data['relatedOrderId'])) {
                    $order = $this->em->getRepository(Order::class)->find($data['relatedOrderId']);
                    if ($order !== null) {
                        $entity->addOrder($order);
                    }
                }
                $this->em->persist($entity);
                break;

            case 'order':
                $entity = new Order();
                $entity->setThirdParty($thirdParty);
                if (!empty($data['documentId'])) {
                    $entity->setOrderId($data['documentId']);
                }
                if (!empty($data['documentDate'])) {
                    $entity->setOrderDate(new \DateTime($data['documentDate']));
                }
                $this->em->persist($entity);
                break;

            default:
                return new JsonResponse(['error' => 'Unknown document type: ' . $documentType], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return new JsonResponse([
            'success'    => true,
            'entityId'   => $entity->getId(),
            'entityType' => $documentType,
        ]);
    }

    private function analyzeWithMistral(string $filePath, string $apiKey): array
    {
        $pdfContent = base64_encode(file_get_contents($filePath));

        $prompt = <<<PROMPT
You are a document analysis assistant. Analyze the provided PDF document and extract the following information in JSON format:

1. "documentType": one of "invoice", "order", or "delivery"
2. "documentId": the document reference/ID number if found
3. "documentDate": the document date in YYYY-MM-DD format if found
4. "thirdPartyName": the customer or supplier name
5. "thirdPartyAddress": the customer or supplier address
6. "relatedDocumentId": any referenced document number (e.g., order number on an invoice)
7. "lines": an array of line items, each with:
   - "description": item description
   - "quantity": numeric quantity
   - "measure": unit of measure (e.g., "pcs", "kg")
   - "grossPrice": gross price per unit
   - "netPrice": net price per unit
   - "vat": VAT percentage as number (e.g., 19)
8. "totalAmount": total amount of the document

Return ONLY valid JSON, no explanation.
PROMPT;

        $payload = [
            'model'    => 'mistral-small-latest',
            'messages' => [
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type'      => 'image_url',
                            'image_url' => [
                                'url' => 'data:application/pdf;base64,' . $pdfContent,
                            ],
                        ],
                    ],
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init('https://api.mistral.ai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException('cURL error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException('Mistral API returned HTTP ' . $httpCode . ': ' . $response);
        }

        $apiResult = json_decode($response, true);
        $content   = $apiResult['choices'][0]['message']['content'] ?? '{}';

        $extracted = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse Mistral response as JSON');
        }

        return $extracted;
    }
}
