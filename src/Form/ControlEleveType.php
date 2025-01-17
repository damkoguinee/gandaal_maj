<?php

namespace App\Form;

use App\Entity\ControlEleve;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ControlEleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $control = $options["data"];
        $builder
            
            
            ->add('etat', ChoiceType::class,[
                "choices"       =>  [
                    "justifié"           =>"justifié",
                    "non justifié"           =>"non justifié",
                ],
                "label"     =>"Statut*",
                'required' => true,
                'placeholder' => "selectionnez le statut"
            ])
            ->add('commentaireJustificatif', TextareaType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le motif ne doit pas contenir plus de 255 caractères",
                        
                        "minMessage"    => "Le motif ne doit pas contenir moins de 4 caractères"
                    ]),
                    new NotBlank(["message" => "Le motif ne peut pas être vide !"])
                ],
                "required"  => true,
                "label"     => "Motif*"
            ])            

            ->add("justificatif", FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "image/jpeg", "image/gif", "image/png", "application/pdf" ],
                        "mimeTypesMessage" => "Formats acceptés : gif, jpg, png, pdf",
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 5 Mo"
                    ])
                ],
                'label' => "justificatif (photo ou un fichier PDF)",
                "help" => "Formats autorisés : images jpg, png, gif ou pdf"
            ])

            ->add('dateJustificatif', DateTimeType::class, [
                'label' => 'Date opération*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d\TH:i'), // Limiter la sélection à la date et l'heure actuelles ou antérieures
                ],
                'html5' => true, // Pour activer le support HTML5
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ControlEleve::class,
        ]);
    }
}
