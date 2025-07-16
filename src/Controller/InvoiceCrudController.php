<?php

namespace Prolyfix\ProcurementBundle\Controller;

use App\Entity\Media;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Prolyfix\ProcurementBundle\Entity\Invoice;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Prolyfix\ProcurementBundle\Form\ParserType;
use Symfony\Component\HttpFoundation\Request;
use Mindee\Client;
use Mindee\Product\Invoice\InvoiceV4;


class InvoiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Invoice::class;
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
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
