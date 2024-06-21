<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class)
        ->add('description', TextareaType::class, 
        [
            'required' => false, // Rend ce champ facultatif
        ])
        ->add('prix', MoneyType::class, 
        [
            'currency' => 'EUR', // Assurez-vous que la devise correspond à vos besoin
        ])
        ->add('imageFile', VichFileType::class, 
        [
            'required' => false,
            'allow_delete' => false, // Permet de supprimer le fichier via le formulaire
            'download_uri' => false, // Permet de télécharger le fichier directement depuis le formulaire
            // 'image_uri' n'est pas une option valide pour VichFileType, donc elle a été supprimée
            'label' => 'Image du Produit',
        ])
        ->add('categorie', EntityType::class, 
        [
            'class' => Categorie::class,
            'choice_label' => 'nom', // Utilisez 'nom' ou tout autre propriété pertinente de l'entité Categorie pour l'affichag
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}