<?php

namespace App\Form;

use App\Entity\Horaire;
use App\Entity\Frequence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class HoraireFrequenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $horaires = $options['data']['horaires'] ?? [];
        $frequence = $options['data']['frequence'] ?? null;

        // Ajout du champ de fréquence
        $builder->add('frequence', IntegerType::class, 
        [
            'data' => $frequence ? $frequence->getIntervalle() : null,
        ]);

        // Ajout du champ pour la date butoir
        $builder->add('dateButoir', IntegerType::class, 
        [
            'required' => false,
            'attr' => ['min' => 1],// Assurer que l'utilisateur entre un nombre positif
            'data' => $frequence->getDateButoir(), 
        ]);

        foreach ($horaires as $horaire) 
        {
            $jour = $horaire->getJour();
            $builder
                ->add("ouvertureMatin_$jour", TimeType::class, 
                [
                    'label' => "Ouverture Matin ($jour)",
                    'data' => $horaire->getOuvertureMatin(),
                    'required' => false,
                ])
                ->add("fermetureMatin_$jour", TimeType::class, 
                [
                    'label' => "Fermeture Matin ($jour)",
                    'data' => $horaire->getFermetureMatin(),
                    'required' => false,
                ])
                ->add("ouvertureSoir_$jour", TimeType::class, 
                [
                    'label' => "Ouverture Soir ($jour)",
                    'data' => $horaire->getOuvertureSoir(),
                    'required' => false,
                ])
                ->add("fermetureSoir_$jour", TimeType::class, 
                [
                    'label' => "Fermeture Soir ($jour)",
                    'data' => $horaire->getFermetureSoir(),
                    'required' => false,
                ])
                ->add('status_' . $jour, ChoiceType::class, 
                [
                    'choices' => [
                        'Ouvert' => 'Ouvert',
                        'Fermé' => 'Fermé',
                        'Fermé Matin' => 'Fermé Matin',
                        'Fermé Soir' => 'Fermé Soir',
                        'Continu' => 'Continu',
                    ],
                    'data' => $horaire->getStatus(),
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // 'data_class' => Horaire::class,
        ]);
    }
}