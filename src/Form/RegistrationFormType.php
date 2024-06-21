<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(
            FormBuilderInterface $builder, 
            array $options): void
    {
        $builder
            ->add('nom', TextType::class, 
            [
                'attr'=>
                [
                    'placeholder' => 'Entrer votrre nom',
                ],	
            ])
            
            ->add('prenom', TextType::class, 
            [
                'attr'=>
                [
                    'placeholder' => 'Entrer votrre prénom',
                ],	
            ])
            ->add('telephone', TextType::class, 
            [
                'constraints' => new Regex(
                [
                    'pattern' => '/^(06|07)\d{8}$/',
                    'message' => 'Le numéro de téléphone doit commencer par 06 ou 07 et contenir 10 chiffres au total.',
                ]),
                'attr' => 
                [
                    'placeholder' => 'Entrez votre numéro de téléphone'
                ],
            ])
            ->add('email', EmailType::class, 
            [
                'constraints' => 
                [
                    new NotBlank(
                    [
                        'message' => 'Veuillez saisir une adresse email.',
                    ]),
                    new Email(
                    [
                        'message' => 'Email saisi non valide.',
                    ]),
                ],
                'attr' => 
                [
                    'placeholder' => 'Votre adresse email'
                ],
            ])

            // ->add('agreeTerms', CheckboxType::class, 
            // [
            //                     'mapped' => false,
            //     'constraints' => [
            //         new IsTrue([
            //             'message' => 'You should agree to our terms.',
            //         ]),
            //     ],
            // ])
            
            ->add('plainPassword', RepeatedType::class, 
            [
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => 
                [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                
                'first_options'=>
                [
                    'label'=>false,
                    // 'attr'=>
                    // [
                    //     'placeholder' => 'Votre mot de passe',
                    // ],
                ],

                'second_options'=>
                [
                    'label'=>false,
                    // 'attr'=>
                    // [
                    //     'placeholder' => 'Confirmer votre mot de passe',
                    // ],
                ],

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
