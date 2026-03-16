<?php

namespace Prolyfix\ProcurementBundle\Controller;

use Prolyfix\HolidayAndTime\Entity\Media;
use Prolyfix\HolidayAndTime\Controller\Admin\BaseCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Prolyfix\ProcurementBundle\Entity\Invoice;
use Prolyfix\ProcurementBundle\Form\ParserType;
use Symfony\Component\HttpFoundation\Request;
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
        yield IdField::new('id')->hideOnForm();
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
