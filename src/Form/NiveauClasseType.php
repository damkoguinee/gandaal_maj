<?php

namespace App\Form;

use App\Entity\Cursus;
use App\Entity\NiveauClasse;
use App\Repository\CursusRepository;
use Symfony\Component\Form\AbstractType;
use App\Repository\NiveauClasseRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NiveauClasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $builder
            ->add('nom', null, [
                "label"     => "Désignation*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  100,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 100 caractères"
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide"])
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
                "placeholder"       =>"Selectionner le cursus",
                "required" => true,
                "label" => 'Cursus',
                'query_builder' => function (CursusRepository $cursusRep) use ($etablissement) {
                    return $cursusRep->createQueryBuilder('c')
                        ->where('c.etablissement = :etablissement')
                        ->setParameter('etablissement', $etablissement);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NiveauClasse::class,
            'etablissement' => null
        ]);
    }
}
