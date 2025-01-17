<?php

namespace App\Form;

use App\Entity\Matiere;
use App\Entity\Enseignant;
use App\Entity\Etablissement;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class EnseignantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options["data"];
        $builder
            ->add('roles', ChoiceType::class,[
                "choices"       =>  [
                    "Enseignant"           =>"ROLE_ENSEIGNANT",
                    "Suppression"    =>"ROLE_SUPPRESSION",
                    "Modification"    =>"ROLE_MODIFICATION",
                ],
                "multiple"  =>true,
                "expanded"  =>true,
                "label"     =>"Niveau d'accès*"
            ])
            ->add('username', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  180,
                        "maxMessage"    =>  "Le pseudo ne doit pas contenir plus de 180 caractères",
                        
                    ]),
                    // new NotBlank(["message" => "le pseudo ne peut pas être vide !"])
                ],
                "required"  =>false,
                "label"     =>"Identifiant"
            ])
            ->add('password', null, [
                "mapped"        =>false,
                "required"      => $user->getId() ? false : false,
                "label"     =>"Mot de passe"

            ])
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
                "required"  =>true,
                "label"     =>"Adresse*"
            ])
            ->add('ville',Null, [
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
            
            // ->add('pays', ChoiceType::class, [
            //     'choices' => $this->getPaysChoices(),
            //     'placeholder' => 'Sélectionnez un pays',
            //     'label' => 'Pays*',
            //     'data' => 'GN',
            // ])
            ->add('pays', Null, [
                'label' => 'Pays*',
                'data' => 'Guinée',
                "required"  => true,
            ])
            ->add('statut', ChoiceType::class,[
                "choices"       =>  [
                    "actif"           =>"actif",
                    "inactif"           =>"inactif",
                ],
                "label"     =>"Statut*",
                'required' => true
            ])
            ->add('matricule',Null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  20,
                        "maxMessage"    =>  "Le matricule ne doit pas contenir plus de 20 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Matricule"
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
                
                'label' => 'Date de naissance'
            ])
            ->add('lieuNaissance', Null, [
                'label' => 'Lieu de naissance',
                'data' => 'Guinée',
                'required' => false
            ])

            ->add('nationalite', Null, [
                'label' => 'Nationalité',
                'data' => 'Guinéenne',
                'required' => false
            ])

            ->add('dateDebut', null, [
                'widget' => 'single_text',
                'required' => true,
                'label' => "date d'embauche*"
            ])
            ->add('dateFin', null, [
                'widget' => 'single_text',
                'required' => false,
                'label' => "date de fin"
            ])
            ->add('numeroCompte',Null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "Le numéro de compte ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Numéro de Compte"
            ])
            ->add('agenceBanque',Null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "L'agence bancaiare ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  => false,
                "label"     =>"Agence Bancaire"
            ])
            ->add('niveau', ChoiceType::class,[
                "choices"       =>  [
                    "crèche"           =>"crèche",
                    "maternelle"           =>"maternelle",
                    "primaire"           =>"primaire",
                    "secondaire"           =>"secondaire",
                ],
                "label"     =>"Niveau",
                'required' => false
            ])
            
            // ->add('matiere', EntityType::class, [
            //     'class' => Matiere::class,
            //     'choice_label' => 'nom',
            //     'multiple' => true,
            //     'label' => "Matières"
            // ])

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
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enseignant::class,
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
