<?php

namespace Prolyfix\ProcurementBundle\Controller;

use Prolyfix\HolidayAndTime\Controller\Admin\BaseCrudController;
use Prolyfix\HolidayAndTime\Form\MediaType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Prolyfix\ProcurementBundle\Entity\DeliverySlip;
use Prolyfix\ProcurementBundle\Entity\Invoice;
use Prolyfix\ProcurementBundle\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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

    public function board()
    {
        $orders = $this->em->getRepository(Order::class)->findBy([], ['creationDate' => 'DESC']);
        $deliverySlips = $this->em->getRepository(DeliverySlip::class)->findBy([], ['creationDate' => 'DESC']);
        $invoices = $this->em->getRepository(Invoice::class)->findBy([], ['creationDate' => 'DESC']);

        return $this->render('@ProlyfixProcurement/order/board.html.twig', [
            'columns' => [
                'order' => array_map(
                    fn (Order $order) => $this->buildOrderCard($order),
                    array_filter(
                        $orders,
                        static fn (Order $order) => $order->getInvoice() === null && $order->getDeliverySlips()->count() === 0
                    )
                ),
                'delivered' => array_map(
                    fn (DeliverySlip $deliverySlip) => $this->buildDeliverySlipCard($deliverySlip),
                    array_filter(
                        $deliverySlips,
                        static fn (DeliverySlip $deliverySlip) => $deliverySlip->getInvoice() === null
                    )
                ),
                'invoiced' => array_map(
                    fn (Invoice $invoice) => $this->buildInvoiceCard($invoice),
                    array_filter(
                        $invoices,
                        static fn (Invoice $invoice) => !$invoice->isPaid()
                    )
                ),
                'paid' => array_map(
                    fn (Invoice $invoice) => $this->buildInvoiceCard($invoice),
                    array_filter(
                        $invoices,
                        static fn (Invoice $invoice) => (bool) $invoice->isPaid()
                    )
                ),
            ],
        ]);
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

    private function buildOrderCard(Order $order): array
    {
        return [
            'documentType' => 'order',
            'reference' => $order->getOrderId() ?: sprintf('Order #%d', $order->getId()),
            'thirdParty' => $order->getThirdParty()?->getName(),
            'amount' => $order->getPrice(),
            'items' => $this->extractOrderItems($order),
            'status' => $order->getState() ?: 'new',
            'detailUrl' => $this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction('detail')
                ->setEntityId($order->getId())
                ->generateUrl(),
        ];
    }

    private function buildDeliverySlipCard(DeliverySlip $deliverySlip): array
    {
        return [
            'documentType' => 'deliverySlip',
            'reference' => $deliverySlip->getDeliverySlipId() ?: sprintf('Delivery slip #%d', $deliverySlip->getId()),
            'thirdParty' => $deliverySlip->getThirdParty()?->getName(),
            'amount' => $deliverySlip->getPrice(),
            'items' => $this->extractDeliverySlipItems($deliverySlip),
            'status' => $deliverySlip->getState() ?: 'pending',
            'detailUrl' => $this->adminUrlGenerator
                ->unsetAll()
                ->setController(DeliverySlipCrudController::class)
                ->setAction('detail')
                ->setEntityId($deliverySlip->getId())
                ->generateUrl(),
        ];
    }

    private function buildInvoiceCard(Invoice $invoice): array
    {
        return [
            'documentType' => 'invoice',
            'reference' => sprintf('Invoice #%d', $invoice->getId()),
            'thirdParty' => $this->resolveInvoiceThirdParty($invoice),
            'amount' => $invoice->getTotalAmount() ?? $invoice->getTotal(),
            'items' => $this->extractInvoiceItems($invoice),
            'status' => $invoice->isPaid() ? 'paid' : 'open',
            'detailUrl' => $this->adminUrlGenerator
                ->unsetAll()
                ->setController(InvoiceCrudController::class)
                ->setAction('detail')
                ->setEntityId($invoice->getId())
                ->generateUrl(),
        ];
    }

    private function extractOrderItems(Order $order): array
    {
        $items = [];

        foreach ($order->getOrderLines() as $line) {
            $label = $line->getProduct()?->getName() ?: $line->getOrderLine();
            if ($label) {
                $items[] = $label;
            }
        }

        if ($items === [] && $order->getSingleProduct()) {
            $items[] = $order->getSingleProduct();
        }

        return array_values(array_unique($items));
    }

    private function extractDeliverySlipItems(DeliverySlip $deliverySlip): array
    {
        $items = [];

        foreach ($deliverySlip->getDeliverySlipLines() as $line) {
            $label = $line->getProduct()?->getName() ?: $line->getDescription();
            if ($label) {
                $items[] = $label;
            }
        }

        return array_values(array_unique($items));
    }

    private function extractInvoiceItems(Invoice $invoice): array
    {
        $items = [];

        foreach ($invoice->getInvoiceLines() as $line) {
            if ($line->getDescription()) {
                $items[] = $line->getDescription();
            }
        }

        if ($items !== []) {
            return array_values(array_unique($items));
        }

        foreach ($invoice->getOrders() as $order) {
            $items = array_merge($items, $this->extractOrderItems($order));
        }

        foreach ($invoice->getDeliverySlips() as $deliverySlip) {
            $items = array_merge($items, $this->extractDeliverySlipItems($deliverySlip));
        }

        return array_values(array_unique($items));
    }

    private function resolveInvoiceThirdParty(Invoice $invoice): ?string
    {
        foreach ($invoice->getOrders() as $order) {
            $name = $order->getThirdParty()?->getName();
            if ($name) {
                return $name;
            }
        }

        foreach ($invoice->getDeliverySlips() as $deliverySlip) {
            $name = $deliverySlip->getThirdParty()?->getName();
            if ($name) {
                return $name;
            }
        }

        return null;
    }
}
