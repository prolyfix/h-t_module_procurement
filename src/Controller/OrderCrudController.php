<?php

namespace Prolyfix\ProcurementBundle\Controller;

use App\Controller\Admin\BaseCrudController;
use App\Form\MediaType;
use Doctrine\DBAL\SQL\Parser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Prolyfix\ProcurementBundle\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\Types\Boolean;
use Prolyfix\CrmBundle\Entity\ThirdParty;
use Prolyfix\ProcurementBundle\Entity\ShoppingList;
use Prolyfix\ProcurementBundle\Form\OrderLineType;
use Prolyfix\ProcurementBundle\Form\ParserType;
use Symfony\Component\HttpFoundation\Request;

class OrderCrudController extends BaseCrudController    
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplates([
                'crud/detail' => '@ProlyfixProcurement/order/detail.html.twig',
                'crud/new' => '@ProlyfixProcurement/order/new.html.twig',
            ])
            ->setFormThemes(['@ProlyfixProcurement/order/form.html.twig']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('creationDate')
            ->add('isPaid')
            ->add('isDelivered')
            ->add('isSprechStundeBedarf')
            ->add('thirdParty');
    }

    public function configureFields(string $pageName): iterable
    {
        return [

            DateField::new('creationDate')->setFormat('dd.MM.yyyy')->hideOnForm(),
            AssociationField::new('thirdParty')->renderAsNativeWidget()->setFormTypeOption('query_builder', function ($entity) {
                return $entity->createQueryBuilder('m')
                    ->orderBy('m.name', 'ASC')
                    ;
            }),
            CollectionField::new('orderLines')->setEntryType(OrderLineType::class)
                ->setFormTypeOption('by_reference', false)->setTemplatePath('@ProlyfixProcurement/order/order_lines.html.twig'),            
            NumberField::new('price')->hideOnForm() ,
            BooleanField::new('isPaid'),
            BooleanField::new('isDelivered'),
            BooleanField::new('isSprechStundeBedarf')
        ];
    }

    public function detail(AdminContext $context)
    {
        $response = parent::detail($context);
        $mediForm = $this->createForm(MediaType::class);
        $response->set('media_form', $mediForm->createView());
        return $response;
    }

    public function parseDoc(AdminContext $context)
    {
        $form = $this->createForm(ParserType::class);
        return $this->render('@ProlyfixProcurement/order/doc_parser.html.twig', [
            'order' => $context->getEntity()->getInstance(),
            'form' => $form->createView(),
        ]);
    }

    public function new(AdminContext $context)
    {
        $request = $context->getRequest();
        $commentable = null;
        if ($request->get('shoppingListId')) {
            $commentable = $this->em->getRepository(ShoppingList::class)->findOneById($request->get('shoppingListId'));
            if($context->getEntity()->getInstance()!==null)
                $context->getEntity()->getInstance()->addShoppingList($commentable); 
        }
        
        $response =  parent::new($context);
        if($commentable !== null){
            $context->getEntity()->getInstance()->addShoppingList($commentable);
            $this->em->persist($context->getEntity()->getInstance());
            $this->em->flush();
            if($response::class == 'Symfony\Component\HttpFoundation\RedirectResponse'){
                return $this->redirectToRoute('admin');
            }
        }
        if($response::class == 'Symfony\Component\HttpFoundation\RedirectResponse'){
            return $this->redirectToRoute('admin',[
                'crudAction' => 'detail',
                'crudControllerFqcn' => 'Prolyfix\ProcurementBundle\Controller\OrderCrudController',
                'entityId' => $context->getEntity()->getInstance()->getId(),
            ]);
        }
        return $response;
    }

    public function tabThirdParty(Request $request)
    {
        $entityId = $request->get('entityId');
        $offset = $request->get('offset')??0;
        $limit = $request->get('limit')??10;
        $thirdParty = $this->em->getRepository(ThirdParty::class)->findOneById($entityId);
        $orders = $this->em->getRepository(Order::class)->findBy(['thirdParty' => $thirdParty], ['creationDate' => 'DESC'], $limit, $offset);
        return $this->render('@ProlyfixProcurement/order/_third_party_tab.html.twig', [
            'orders' => $orders,
        ]);
    }

    public function documentScanner(Request $request)
    {
        $order = $this->em->getRepository(Order::class)->findOneById($request->get('orderId'));
        $form = $this->createForm(ParserType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file) {
                // Process the file and extract data
                // ...
            }
        }
        return $this->render('@ProlyfixProcurement/order/document_scanner.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }
}
