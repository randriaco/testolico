<?php

namespace App\Form;

use App\Entity\TableChaise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class TableChaiseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('numeroTable', IntegerType::class)
        ->add('nombreChaise', IntegerType::class)
        ->add('emplacement', ChoiceType::class, 
        [
            'choices' => [
                'RDC' => 'rdc',
                'Terrasse' => 'terrasse',
                'Etage' => 'etage',
                'Autre' => 'autre',
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TableChaise::class,
        ]);
    }
}
