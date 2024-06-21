<?php

namespace App\Form;

use App\Entity\Place;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, 
            [
                'widget' => 'single_text', // Permet de sélectionner une date via un calendrier
                'format' => 'yyyy-MM-dd',  // Format de la date
                // 'label' => 'Début de Fermeture', // Label du champ
                // 'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap
            ])
            ->add('placesTotal', IntegerType::class)
            ->add('placesReservees', IntegerType::class,
            [
                'data' => 0,
            ])
            ->add('placesLiberees', IntegerType::class, 
            
            [
                'data' => 0,
            ])
            ->add('placesDispo', IntegerType::class,
            
            [
                'data' => 0,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
