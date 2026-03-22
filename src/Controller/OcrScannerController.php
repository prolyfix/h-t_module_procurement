<?php

namespace Prolyfix\ProcurementBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Prolyfix\CrmBundle\Entity\ThirdParty;
use Prolyfix\HolidayAndTime\Entity\Media;
use Prolyfix\ProcurementBundle\Entity\DeliverySlip;
use Prolyfix\ProcurementBundle\Entity\DeliverySlipLine;
use Prolyfix\ProcurementBundle\Entity\Invoice;
use Prolyfix\ProcurementBundle\Entity\InvoiceLine;
use Prolyfix\ProcurementBundle\Entity\Order;
use Prolyfix\ProcurementBundle\Form\ParserType;
use Smalot\PdfParser\Parser;
use SoftCreatR\MistralAI\MistralAI;
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

    #[\Symfony\Component\Routing\Annotation\Route('/pdf/{id}', name: 'serve_pdf', methods: ['GET'])]
    public function servePdf(int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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

        try {
            $result = $this->analyzeWithMistral($filePath);
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
                if (!empty($data['relatedOrderId'])) {
                    $order = $this->em->getRepository(Order::class)->find($data['relatedOrderId']);
                    if ($order !== null) {
                        $entity->addOrder($order);
                    }
                }
                if (!empty($data['totalAmount'])) {
                    $entity->setTotalAmount((float) $data['totalAmount']);
                }
                if (!empty($data['mediaId'])) {
                    $media = $this->em->getRepository(Media::class)->find($data['mediaId']);
                    if ($media !== null) {
                        $entity->setSourceDocument($media);
                    }
                }
                foreach ($lines as $lineData) {
                    $line = new InvoiceLine();
                    $line->setDescription($lineData['description'] ?? null);
                    $line->setQuantity(isset($lineData['quantity']) ? (float) $lineData['quantity'] : null);
                    $line->setMeasure($lineData['measure'] ?? null);
                    $line->setGrossPrice(isset($lineData['grossPrice']) ? (float) $lineData['grossPrice'] : null);
                    $line->setNetPrice(isset($lineData['netPrice']) ? (float) $lineData['netPrice'] : null);
                    $line->setVat(isset($lineData['vat']) ? (float) $lineData['vat'] : null);
                    $entity->addInvoiceLine($line);
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

    private function analyzeWithMistral(string $filePath): array
    {
        $apiKey = trim((string) ($_ENV['MISTRAL_API_KEY'] ?? $_SERVER['MISTRAL_API_KEY'] ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('MISTRAL_API_KEY is not configured');
        }

        $pdfText = $this->extractPdfText($filePath);
        if ($pdfText === '') {
            throw new \RuntimeException('The uploaded PDF does not contain extractable text. Please upload a text-based PDF.');
        }

        $prompt = <<<PROMPT
You are a document analysis assistant specializing in procurement documents (incoming invoices, delivery slips, and purchase orders).

IMPORTANT CONTEXT: These documents are received FROM an external supplier/vendor BY a company. The document always contains two parties:
- The ISSUER (sender): the external supplier/vendor who wrote and sent this document. This is the party we need.
- The RECIPIENT (receiver): the company that received the document (our client's own company). IGNORE this party.

The "thirdPartyName" and "thirdPartyAddress" MUST be the ISSUER (the external supplier/vendor who sent the document), NOT the recipient address.
Typically the issuer is shown at the top of the document or in the "From:" section. The recipient appears in "To:", "Bill To:", "Deliver To:" sections — do NOT use those.

Analyze the provided document and extract the following information in JSON format:

1. "documentType": one of "invoice", "order", or "delivery"
2. "documentId": the document reference/ID number if found
3. "documentDate": the document date in YYYY-MM-DD format if found
4. "thirdPartyName": the ISSUER/SENDER name (i.e. the supplier or vendor who issued this document, NOT the recipient)
5. "thirdPartyAddress": the ISSUER/SENDER address (same rule: the supplier's address, NOT the recipient's)
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
                    'content' => $prompt . "\n\nDocument text:\n" . mb_substr($pdfText, 0, 50000),
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        $httpFactory = new HttpFactory();
        $mistral = new MistralAI(
            $httpFactory,
            $httpFactory,
            $httpFactory,
            new Client(),
            $apiKey,
        );

        $response = $mistral->createChatCompletion([], $payload);
        if ($response === null || $response->getStatusCode() >= 300) {
            throw new \RuntimeException('Mistral API returned HTTP ' . ($response?->getStatusCode() ?? 0));
        }

        $apiResult = json_decode((string) $response->getBody(), true);
        $content   = $apiResult['choices'][0]['message']['content'] ?? '{}';

        if (\is_array($content)) {
            $content = implode('', array_map(static fn ($item) => (string) ($item['text'] ?? ''), $content));
        }

        $extracted = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse Mistral response as JSON');
        }

        return $extracted;
    }

    private function extractPdfText(string $filePath): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        return trim($pdf->getText());
    }
}
