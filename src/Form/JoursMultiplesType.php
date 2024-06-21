<?php

namespace App\Form;

use App\Entity\JoursMultiples;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class JoursMultiplesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('debutFermeture', DateType::class, [
            'widget' => 'single_text', // Permet de sélectionner une date via un calendrier
            'format' => 'yyyy-MM-dd',  // Format de la date
            'label' => 'Début de Fermeture', // Label du champ
            'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap
        ])
        ->add('finFermeture', DateType::class, [
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd',
            'label' => 'Fin de Fermeture',
            'attr' => ['class' => 'form-control']
        ])
        ->add('motif', TextType::class, [
            'label' => 'Motif de la fermeture',
            'attr' => ['class' => 'form-control']
        ])
        // ->add('save', SubmitType::class, ['label' => 'Ajouter', 'attr' => ['class' => 'btn btn-primary']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JoursMultiples::class,
        ]);
    }
}