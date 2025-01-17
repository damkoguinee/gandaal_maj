<?php

namespace App\Form;

use App\Entity\Eleve;
use App\Entity\DocumentEleve;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DocumentEleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('type', ChoiceType::class, [
            'choices' => [
                'Livret scolaire' => 'livret scolaire',
                'Acte de naissance' => 'acte de naissance',
                "Pièce d'identité" => "pièce d'identité",
                'Autre document' => 'autre document'
            ],
            'placeholder' => 'Sélectionnez le type de document',
            'label' => 'Type de document',
            'required' => false,
        ])

        ->add("nom", FileType::class, [
            "mapped"        =>  false,
            "required"      => false,
            "constraints"   => [
                new File([
                    "mimeTypes" => [ "application/pdf" ],
                    "mimeTypesMessage" => "Format accepté : PDF",
                    "maxSize" => "2048k",
                    "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                ])
            ],
            'label' =>"joindre un document",
            "help" => "Formats autorisés : PDF"
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentEleve::class,
        ]);
    }
}
