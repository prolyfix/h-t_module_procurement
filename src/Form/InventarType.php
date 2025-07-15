<?php

namespace Prolyfix\ProcurementBundle\Form;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\DBAL\Types\IntegerType;
use Prolyfix\ProcurementBundle\Entity\Inventar;
use Prolyfix\ProcurementBundle\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType as TypeIntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $builder->getData();
        $product = $data->getProduct();
        $builder
            ->add('quantity',TypeIntegerType::class,['attr' => ['class' => 'form-control']])
            ->add('comment',null,['attr' => ['class' => 'form-control']]);
        if($product !== null && $product->hasExpirationDate()){
            $builder->add('expirationDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ]);
        }
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inventar::class,
        ]);
    }
}
