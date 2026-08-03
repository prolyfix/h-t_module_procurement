<?php

namespace Prolyfix\ProcurementBundle\Controller;

use Prolyfix\HolidayAndTime\Entity\Media;
use Prolyfix\HolidayAndTime\Controller\Admin\BaseCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Prolyfix\ProcurementBundle\Entity\Invoice;
use Prolyfix\ProcurementBundle\Form\ParserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mindee\Client;
use Mindee\Product\Invoice\InvoiceV4;


class InvoiceCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            yield IdField::new('id');
            yield TextField::new('supplierName', 'Supplier')
                ->formatValue(static function ($value, Invoice $invoice) {
                    foreach ($invoice->getOrders() as $order) {
                        if ($order->getThirdParty() !== null && $order->getThirdParty()->getName() !== null) {
                            return $order->getThirdParty()->getName();
                        }
                    }

                    return '-';
                })
                ->onlyOnIndex();
            yield MoneyField::new('totalAmount', 'Amount')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->formatValue(static function ($value, Invoice $invoice) {
                    return $invoice->getTotalAmount() ?? $invoice->getTotal();
                });
            yield IntegerField::new('itemsCount', 'Items')
                ->formatValue(static fn ($value, Invoice $invoice) => $invoice->getInvoiceLines()->count())
                ->onlyOnIndex();
            yield BooleanField::new('isPaid', 'Bezahlt');

            return;
        }

        yield IdField::new('id')->hideOnForm();
        yield MoneyField::new('totalAmount', 'Amount')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
        yield BooleanField::new('isPaid', 'Bezahlt');

        if (class_exists('Prolyfix\BankingBundle\Entity\Entry')) {
            yield IntegerField::new('bankingEntryId', 'Banking-Eintrag (ID)')
                ->setHelp('ID des zugehörigen Bankbuchungs-Eintrags')
                ->setRequired(false);
        }
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud->overrideTemplates([
            'crud/detail' => '@ProlyfixProcurement/invoice/detail.html.twig',
        ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $downloadPdfAction = Action::new('downloadPdf', 'PDF')
            ->setIcon('fa fa-file-pdf')
            ->linkToCrudAction('downloadPdf');

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $downloadPdfAction)
            ->add(Crud::PAGE_DETAIL, $downloadPdfAction);
    }

    public function downloadPdf(AdminContext $context, Pdf $knpSnappyPdf): Response
    {
        $this->assertListAccessForEntity(Invoice::class, 'You are not allowed to download this invoice.');

        $invoice = $context->getEntity()->getInstance();
        if (!$invoice instanceof Invoice) {
            throw $this->createNotFoundException('Invoice not found.');
        }

        $html = $this->renderView('@ProlyfixProcurement/invoice/pdf.html.twig', [
            'invoice' => $invoice,
        ]);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            sprintf('invoice-%d.pdf', $invoice->getId()),
            'application/pdf',
            'attachment'
        );
    }

    public function parseDoc(AdminContext $context, Request $request, EntityManagerInterface $em)
    {
        $media = new Media();
        $form = $this->createForm(ParserType::class, $media);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($media);
            $em->flush();
            $mindeeClient = new Client("2d63b1457648f819a85b9b56d389ce8a");

            // Load a file from disk
            $inputSource = $mindeeClient->sourceFromPath(__DIR__."/../../../../../private/medias/".$media->getFileName());
            // Parse the file
            $apiResponse = $mindeeClient->parse(InvoiceV4::class, $inputSource);
            $response =  $apiResponse->document;

            // Process the file and extract data
            // ...
            // Redirect or render a response
        }
        return $this->render('@ProlyfixProcurement/order/doc_parser.html.twig', [
            'order' => $context->getEntity()->getInstance(),
            'form' => $form->createView(),
        ]);
    }
}
