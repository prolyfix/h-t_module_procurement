<?php
namespace Prolyfix\ProcurementBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ComparisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultFactor',ChoiceType::class,['attr' => ['class' => 'form-control'],'choices'=>[1=>1,'Schwellenwert'=>'Schwellenwert','Grenzwert'=>'Grenzwert']])
            ->add('entrySequence',null,['attr' => ['class' => 'form-control']])
            ->add('compareSequence',EntityType::class,['attr' => ['class' => 'form-control','placeholder' => 'Select a sequence'],
            'class' => 'Prolyfix\GoaBundle\Entity\Sequence',
            'choice_label' => 'name',
            'multiple' => true,
            
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}