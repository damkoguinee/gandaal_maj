<?php

namespace App\Form;

use App\Entity\Cursus;
use App\Entity\Formation;
use App\Entity\NiveauClasse;
use App\Repository\CursusRepository;
use App\Repository\FormationRepository;
use Symfony\Component\Form\AbstractType;
use App\Repository\NiveauClasseRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                "label"     => "Nom de la formation*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  150,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 150 caractères"
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide"])
                ]
            ])

            ->add('classe', null, [
                "label"     => "Niveau*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  50,
                        "maxMessage"   =>  "Le niveau ne doit pas depassé 50 caractères"
                    ]),
                    new NotBlank(["message" => "le niveau ne peut pas être vide"])
                ]
            ])
            ->add('code', null, [
                "label"     => "Code*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  15,
                        "maxMessage"   =>  "Le code ne doit pas depassé 15 caractères"
                    ]),
                    new NotBlank(["message" => "le code ne peut pas être vide"])
                ]
            ])

            ->add('cursus', EntityType::class, [
                'class' => Cursus::class,
                "choice_label"  =>  function(Cursus $a){
                    return $a->getNom();
                },
                "placeholder" => "Selectionner le cursus",
                "required" => true,
                "label" => "Cursus*",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
