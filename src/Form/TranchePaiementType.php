<?php

namespace App\Form;

use App\Entity\Etablissement;
use App\Entity\TranchePaiement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class TranchePaiementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                "label"     => "Nom de la tranche*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  100,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 100 caractères"
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide"])
                ]
            ])
            // ->add('etablissement', EntityType::class, [
            //     'class' => Etablissement::class,
            //     'choice_label' => 'nom',
            //     'required' => true,
            //     'label' => "Etablissement*"
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TranchePaiement::class,
        ]);
    }
}
