<?php

namespace Prolyfix\ProcurementBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\ProcurementBundle\Entity\Inventar;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Util\Json;
use Prolyfix\ProcurementBundle\Entity\Product;
use Prolyfix\ProcurementBundle\Form\InventarType;
use Prolyfix\ProcurementBundle\Repository\InventarRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InventarCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Inventar::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('creationDate'),
            AssociationField::new('product'),
            NumberField::new('quantity'),
            TextField::new('comment'),
        ];
    }

    public function add(Request $request, EntityManagerInterface $em):Response
    {
        $productId = $request->get('productId');
        $inventar = (new Inventar());
        if($productId){
            $product = $em->getRepository(Product::class)->find($productId);
            if(!$product)
                throw new \Exception('Product not found');
                $inventar->setProduct($product);
                $form = $this->createForm(InventarType::class, $inventar);
        }else{
            $product = new Product();
            $form = $this->createForm(InventarType::class, $inventar);
            $form->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'form-control']
            ]);
        }
        
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($inventar);
            $em->flush();
            return new JsonResponse(['status' => 'success']);
        }
        return $this->render('common/simpleForm.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function show(InventarRepository $repo):Response
    {
        $inventory = $repo->getOverview();
        $inventarType = $this->createForm(InventarType::class, (new Inventar()));
        return $this->render('@ProlyfixProcurementBundle/inventar/show.html.twig', [
            'inventory' => $inventory,
            'form' => $inventarType->createView()
        ]);
    }

}
