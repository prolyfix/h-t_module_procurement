<?php

namespace Prolyfix\ProcurementBundle\Form;

use Prolyfix\ProcurementBundle\Entity\Order;
use Prolyfix\ProcurementBundle\Entity\OrderLine;
use Prolyfix\ProcurementBundle\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
            ])->add('orderLine')
            ->add('quantity')
            ->add('grossPrice', null, [
                'attr' => [
                    'data-action' => 'blur->invoice#calculateNet',
                ],
            ])
            ->add('netPrice', null, [
                'attr' => [
                    'data-action' => 'blur->invoice#calculateGross',
                ],
            ])
            ->add('vat', ChoiceType::class, [
                'choices' => [
                    '0%' => 0,
                    '7%' => 7,
                    '19%' => 19,
                ],
                'data' => 19,
                'attr' => [
                    'data-action' => 'change->invoice#calculateGross',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
        ]);
    }
}
