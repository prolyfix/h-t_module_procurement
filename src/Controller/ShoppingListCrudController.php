<?php

namespace Prolyfix\ProcurementBundle\Controller;

use App\Controller\Admin\BaseCrudController;
use Prolyfix\ProcurementBundle\Entity\ShoppingList;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ShoppingListCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return ShoppingList::class;
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
