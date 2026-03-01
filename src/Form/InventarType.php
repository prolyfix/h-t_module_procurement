<?php

namespace Prolyfix\ProcurementBundle\Form;

use Prolyfix\HolidayAndTime\Entity\Company;
use Prolyfix\HolidayAndTime\Entity\User;
use Doctrine\DBAL\Types\IntegerType;
use Prolyfix\ProcurementBundle\Entity\Inventar;
use Prolyfix\ProcurementBundle\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('comment',null,['attr' => ['class' => 'form-control']])
            ->add('isInventar', CheckboxType::class, [
                'required' => false,
                'label' => 'Inventar',
            ]);
        if($product !== null && ($product->hasPeremption() || $product->hasExpirationDate())){
            $builder->add('expirationDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'label' => 'Date de péremption',
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
