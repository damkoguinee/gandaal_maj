<?php

namespace App\Form;

use App\Entity\Enseignant;
use App\Entity\DocumentEnseignant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DocumentEnseignantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Diplôme' => 'diplôme',
                    'Casier judiciaire' => 'casier_judiciaire',
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
            // ->add('enseignant', EntityType::class, [
            //     'class' => Enseignant::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentEnseignant::class,
        ]);
    }
}
