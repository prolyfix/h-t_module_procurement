<?php

namespace Prolyfix\ProcurementBundle\Controller;

use Prolyfix\HolidayAndTime\Controller\Admin\BaseCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Prolyfix\CrmBundle\Entity\ThirdParty;
use Prolyfix\ProcurementBundle\Entity\DeliverySlip;
use Prolyfix\ProcurementBundle\Form\DeliverySlipLineType;

class DeliverySlipCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return DeliverySlip::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplates([
                'crud/detail' => '@ProlyfixProcurement/delivery_slip/detail.html.twig',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('deliveryDate')
            ->add('state')
            ->add('thirdParty');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('deliverySlipId')->setLabel('procurement.deliverySlipId'),
            DateField::new('deliveryDate')->setFormat('dd.MM.yyyy')->setLabel('procurement.deliveryDate'),
            AssociationField::new('thirdParty')->renderAsNativeWidget()->setFormTypeOption('query_builder', function ($entity) {
                return $entity->createQueryBuilder('m')
                    ->orderBy('m.name', 'ASC');
            }),
            AssociationField::new('procurementOrder')->renderAsNativeWidget()->setLabel('procurement.order'),
            AssociationField::new('invoice')->renderAsNativeWidget()->setLabel('procurement.invoice'),
            ChoiceField::new('state')->setChoices([
                'Pending'   => 'pending',
                'Delivered' => 'delivered',
                'Cancelled' => 'cancelled',
            ])->renderAsNativeWidget(),
            CollectionField::new('deliverySlipLines')
                ->setEntryType(DeliverySlipLineType::class)
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
            NumberField::new('price')->hideOnForm(),
        ];
    }
}
