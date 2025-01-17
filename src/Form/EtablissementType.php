<?php

namespace App\Form;

use App\Entity\Entreprise;
use App\Entity\Etablissement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => 'nom',
            ])
            ->add('lieu', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  50,
                        "maxMessage"    =>  "Le lieu ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Lieu*"
            ])
            ->add('nom', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le nom ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Nom*"
            ])
            ->add('telephone', TelType::class, [
                "constraints"   =>  [
                    new Length([
                        "min"           =>  9,
                        "minMessage"    =>  "Le téléphone ne doit pas contenir moins de 9 ",
                        
                    ]),
                    new NotBlank(["message" => "le numéro téléphone ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Téléphone*"
            ])
            ->add('email',null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "L'email ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Email"
            ])
            ->add('adresse', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le numéro de l'agrement ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Adresse*"
            ])
            ->add('pays', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le pays ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "data" => "Guinée",
                "label"     =>"Pays*"
            ])
            ->add('region', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La région ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Région*"
            ])
            ->add('secteur', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le secteur ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Secteur*"
            ])
            ->add('ire', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "L'IRE ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"IRE*"
            ])
            ->add('dpe', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le DPE ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"DPE*"
            ])
            ->add('initial', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  5,
                        "maxMessage"    =>  "L'initial ne doit pas contenir plus de 5 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Initial*"
            ])
            ->add('devise', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La devise ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Devise*"
            ])
            ->add('agrement', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "L'agrement ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => false,
                "label"     =>"Agrement*"
            ])

            ->add('image', FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "image/jpeg", "image/gif", "image/png" ],
                        "mimeTypesMessage" => "Formats acceptés : gif, jpg, png",
                        "maxSize" => "2048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                'label' =>"Photo du site",
                "help" => "Formats autorisés : images jpg, png ou gif"
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etablissement::class,
        ]);
    }
}
