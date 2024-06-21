<?php

namespace App\Form;

use App\Entity\JourSpecifique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JourSpecifiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, 
            [
                'widget' => 'single_text', // Utilise un sélecteur de date
                'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap 
            ])
            ->add('ouvertureMatin', TimeType::class, 
            [
                'required' => false,
                'widget' => 'single_text',
                'data' => new \DateTime('11:00'),
                'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap 
            ])
            ->add('fermetureMatin', TimeType::class, 
            [
                'required' => false,
                'widget' => 'single_text',
                'data' => new \DateTime('15:00'),
                'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap 
            ])
            ->add('ouvertureSoir', TimeType::class, 
            [
                'required' => false,
                'widget' => 'single_text',
                'data' => new \DateTime('19:00'),
                'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap 
            ])
            ->add('fermetureSoir', TimeType::class, 
            [
                'required' => false,
                'widget' => 'single_text',
                'data' => new \DateTime('22:00'),
                'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Ouvert' => 'Ouvert',
                    'Fermé' => 'Fermé',
                    'Fermé Matin' => 'Fermé Matin',
                    'Fermé Soir' => 'Fermé Soir',
                    'Continu' => 'Continu',
                ],
            ])
            ->add('motif', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'] // Classe CSS pour Bootstrap
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JourSpecifique::class,
        ]);
    }
}
