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

class EnseignantEspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options["data"];
        $builder
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
            ->add('pays', Null, [
                'label' => 'Pays*',
                'data' => 'Guinée',
                "required"  => true,
            ])
            
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
                'attr' => [
                    'max' => '2010-01-01', // Définit la date minimale
                ],
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
