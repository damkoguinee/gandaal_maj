<?php

namespace App\Form;

use App\Entity\Preinscription;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PreinscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $eleve = $options["data"];

        $builder
            
            ->add('nom', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le nom ne doit pas contenir plus de 100 caractères",
                        
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Nom*"
            ])
            ->add('prenom',null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "Le prénom ne doit pas contenir plus de 150 caractères",
                        
                        "minMessage"    =>"Le prénom ne doit pas contenir moins de 4 caractères"
                    ]),
                    new NotBlank(["message" => "le prénom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Prénom*"
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
            ->add('email', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "L'émail ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Adresse émail"
            ])
            ->add('adresse',Null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "L'adresse ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => False,
                "label"     =>"Adresse"
            ])
            ->add('ville', Null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "La ville ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  =>true,
                "data" => "Conakry",
                "label"     =>"Ville*"
            ])
            
            ->add('pays', ChoiceType::class, [
                'choices' => $this->getPaysChoices(),
                'placeholder' => 'Sélectionnez un pays',
                'label' => 'Pays*',
                'data' => 'GN',
            ])
            
           
            ->add('sexe', ChoiceType::class,[
                "choices"       =>  [
                    "Masculin"           =>"m",
                    "Feminin"           =>"f",
                ],
                "label"     =>"Sexe*",
                'required' => true
            ])
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
                'attr' => [
                    'max' => '2023-01-01', // Définit la date minimale
                ],
                'label' => 'Date de naissance'
            ])
            ->add('lieuNaissance', ChoiceType::class, [
                'choices' => $this->getPaysChoices(),
                'placeholder' => 'Sélectionnez un pays',
                'label' => 'Lieu de naissance',
                'data' => 'GN',
            ])
            
            ->add('ecoleOrigine', Null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "L'origine' ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => false,
                "label"     =>"Ecole d'origine"
            ])

            ->add("photo", FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "image/jpeg", "image/gif", "image/png" ],
                        "mimeTypesMessage" => "Formats acceptés : gif, jpg, png",
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                'label' =>"joindre une photo",
                "help" => "Formats autorisés : images jpg, png ou gif"
            ])


            ->add('nomp', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le nom ne doit pas contenir plus de 100 caractères",
                        
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Nom du père*"
            ])
            ->add('prenomp',null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "Le prénom ne doit pas contenir plus de 150 caractères",
                        
                        "minMessage"    =>"Le prénom ne doit pas contenir moins de 4 caractères"
                    ]),
                    new NotBlank(["message" => "le prénom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Prénom du père*"
            ])
            ->add('telephonep', TelType::class, [
                "constraints"   =>  [
                    new Length([
                        "min"           =>  9,
                        "minMessage"    =>  "Le téléphone ne doit pas contenir moins de 9 ",
                        
                    ]),
                ],
                "required"  => false,
                "label"     =>"Téléphone du père*"
            ])
            ->add('emailp', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "L'émail ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Email du père"
            ])

            ->add('professionp', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La proféssion ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Proféssion du père"
            ])

            ->add('lieuTravailp', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le lieu de travail ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Lieu de travail du père"
            ])



            ->add('nomm', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le nom ne doit pas contenir plus de 100 caractères",
                        
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Nom mère*"
            ])
            ->add('prenomm',null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "Le prénom ne doit pas contenir plus de 150 caractères",
                        
                        "minMessage"    =>"Le prénom ne doit pas contenir moins de 4 caractères"
                    ]),
                    new NotBlank(["message" => "le prénom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Prénom mère*"
            ])
            ->add('telephonem', TelType::class, [
                "constraints"   =>  [
                    new Length([
                        "min"           =>  9,
                        "minMessage"    =>  "Le téléphone ne doit pas contenir moins de 9 ",
                        
                    ]),
                ],
                "required"  => false,
                "label"     =>"Téléphone mère*"
            ])
            ->add('emailm', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "L'émail ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Email mère"
            ])

            ->add('professionm', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La proféssion ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Proféssion mère"
            ])

            ->add('lieuTravailm', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le lieu de travail ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Lieu de travail mère"
            ])


            ->add('nomt', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le nom ne doit pas contenir plus de 100 caractères",
                        
                    ]),
                ],
                "required"  => false,
                "label"     =>"Nom tuteur*"
            ])
            ->add('prenomt',null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "Le prénom ne doit pas contenir plus de 150 caractères",
                        
                        "minMessage"    =>"Le prénom ne doit pas contenir moins de 4 caractères"
                    ]),
                ],
                "required"  => false,
                "label"     =>"Prénom tuteur*"
            ])
            ->add('telephonet', TelType::class, [
                "constraints"   =>  [
                    new Length([
                        "min"           =>  9,
                        "minMessage"    =>  "Le téléphone ne doit pas contenir moins de 9 ",
                        
                    ]),
                ],
                "required"  => false,
                "label"     =>"Téléphone tuteur*"
            ])
            ->add('emailt', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "L'émail ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Email tuteur"
            ])

            ->add('professiont', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La proféssion ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Proféssion tuteur"
            ])

            ->add('lieuTravailt', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le lieu de travail ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Lieu de travail tuteur"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Preinscription::class,
        ]);
    }

    private function getPaysChoices()
    {
        $countries = Countries::getNames();

        // Placer la Guinée en première position
        $guinee = ['GN' => 'Guinée'];
        unset($countries['GN']);
        $countries = $guinee + $countries;

        // Tri alphabétique des pays
        asort($countries);

        return array_flip($countries);
    }
}
