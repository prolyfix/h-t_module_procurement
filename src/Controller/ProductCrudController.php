<?php

namespace Prolyfix\ProcurementBundle\Controller;

use App\Controller\Admin\BaseCrudController;
use App\Form\MediaType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Prolyfix\ProcurementBundle\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\Types\Boolean;
use Prolyfix\CrmBundle\Entity\RelatedCommentable;
use Prolyfix\CrmBundle\Form\RelatedCommentableType;
use Prolyfix\ProcurementBundle\Entity\OrderLine;
use Prolyfix\ProcurementBundle\Entity\Product;

class ProductCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextareaField::new('description')->hideOnIndex(),
            ImageField::new('avatarFilename')->setUploadDir('public/uploads/avatar')->setBasePath('uploads/avatar')->setCssClass('index_avatar')  ,
            BooleanField::new('isSprechstundenbedarf'),
            BooleanField::new('hasExpirationDate'),
            NumberField::new('minimalQuantity'),
            ImageField::new('avatarFilename')->setUploadDir('public/uploads/avatar')->setBasePath('uploads/avatar')->hideOnIndex()  ,
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->update(Crud::PAGE_INDEX, 'new', function (Action $action) {
            return $action->setHtmlAttributes([
                'data-action' => 'click->modal-form#openModal',
                ]);
        });


        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
        
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplates([
                'crud/detail' => '@ProlyfixProcurement/product/detail.html.twig',
            ]);
    }
    public function detail(AdminContext $context)
    {
        $response = parent::detail($context);
        $mediForm = $this->createForm(MediaType::class);
        $response->set('media_form', $mediForm->createView());
        $thirdPartyForm = $this->createForm(RelatedCommentableType::class);
        $response->set('third_party_form', $thirdPartyForm->createView());

        $partners = $this->em->getRepository(RelatedCommentable::class)
                    ->findBy(['relatedCommentable' => $context->getEntity()->getInstance()]);
        $response->set('partners', $partners);
        $bestellungen = $this->em->getRepository(OrderLine::class)
                    ->findBy(['product'=>$context->getEntity()->getInstance()]);;
        $response->set('bestellungen', $bestellungen);
        return $response;
    }


}
