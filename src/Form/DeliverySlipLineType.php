<?php

namespace Prolyfix\ProcurementBundle\Form;

use Prolyfix\ProcurementBundle\Entity\DeliverySlipLine;
use Prolyfix\ProcurementBundle\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliverySlipLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'required' => false,
            ])
            ->add('description')
            ->add('quantity')
            ->add('grossPrice')
            ->add('netPrice')
            ->add('vat', ChoiceType::class, [
                'choices' => [
                    '0%'  => 0,
                    '7%'  => 7,
                    '19%' => 19,
                ],
                'data' => 19,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeliverySlipLine::class,
        ]);
    }
}
