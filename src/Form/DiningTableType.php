<?php

namespace App\Form;

use App\Entity\DiningTable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiningTableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, 
            [
                'label' => 'Nom de la table',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nombreChaise', IntegerType::class, 
            [
                'label' => 'Nombre de chaises',
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiningTable::class,
        ]);
    }
}