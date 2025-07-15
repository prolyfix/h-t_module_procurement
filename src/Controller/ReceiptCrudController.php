<?php

namespace Prolyfix\ProcurementBundle\Controller;

use App\Controller\Admin\BaseCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Prolyfix\ProcurementBundle\Entity\Receipt;
use Symfony\Component\Validator\Constraints\File;

class ReceiptCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Receipt::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        $fields =[
            TextField   :: new ('singleProduct')->setLabel('description'),
            NumberField :: new ('total')->setLabel('Betrag'),
            ImageField:: new ('avatarFilename') -> setUploadDir ('public/uploads/avatar') -> setBasePath ('uploads/avatar')->hideOnIndex()
        ];
        if($this->security->isGranted('ROLE_ADMIN')){
          $fields[] = ChoiceField :: new ('state') -> setChoices ([
                'Pending' => 'Pending',
                'Paid' => 'Paid',
                'Cancelled' => 'Cancelled',
            ])->renderAsNativeWidget() ;
            $fields [] = AssociationField :: new ('owner') -> renderAsNativeWidget();
        }
        else{

          $fields[] = ChoiceField :: new ('state') -> setChoices ([
                'Pending' => 'Pending',
                'Paid' => 'Paid',
            ])->hideOnForm() ;
             $fields [] = AssociationField :: new ('owner') -> hideOnForm();
        }
        return $fields;
    }


    
}
