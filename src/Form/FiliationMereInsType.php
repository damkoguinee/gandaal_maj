<?php

namespace App\Form;

use App\Entity\Eleve;
use App\Entity\Tuteur;
use App\Entity\Filiation;
use App\Entity\Etablissement;
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

class FiliationMereInsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                "label"     =>"Nom de la mère*"
            ])
            ->add('prenom', null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "Le prénom ne doit pas contenir plus de 150 caractères",
                        
                        "minMessage"    =>"Le prénom ne doit pas contenir moins de 4 caractères"
                    ]),
                    new NotBlank(["message" => "le prénom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Prénom de la mère*"
            ])
            ->add('telephone', TelType::class, [
                "constraints"   =>  [
                    new Length([
                        "min"           =>  9,
                        "minMessage"    =>  "Le téléphone ne doit pas contenir moins de 9 ",
                        
                    ])
                ],
                "required"  => false,
                "label"     =>"Téléphone du mère"
            ])

            ->add('email')

            ->add('profession', null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La proféssion ne doit pas contenir plus de 100 caractères",
                    ])
                ],
                "required"  => false,
                "label"     =>"Proféssion de la mère"
            ])

            ->add('lieuTravail', null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le lieu de travail ne doit pas contenir plus de 100 caractères",
                    ])
                ],
                "required"  => false,
                "label"     =>"Lieu de travail mère"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filiation::class,
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
