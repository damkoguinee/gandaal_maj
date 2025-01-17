<?php

namespace App\Form;

use App\Entity\Cursus;
use App\Entity\Etablissement;
use App\Entity\FraisInscription;
use App\Repository\CursusRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class FraisInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $builder
            ->add('description', ChoiceType::class, [
                'label' => 'Description*',
                "choices"       =>  [
                    "inscription"           =>"inscription",
                    "réinscription"           =>"réinscription",
                ],
                'required' => true,
            ])
            ->add('montant', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => 'Le montant de base horaire doit être un nombre.',
                    ])
                ],
                // "attr"  =>["placeholder" =>"Entrer le poid"],
                "required"  => true,
                "label"     =>"Montant*"
            ])
            ->add('cursus', EntityType::class, [
                'class' => Cursus::class,
                "choice_label"  =>  function(Cursus $a){
                    return $a->getNom();
                },
                "placeholder"       =>"Selectionner le cursus*",
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
            'data_class' => FraisInscription::class,
            'etablissement' => null
        ]);
    }
}
