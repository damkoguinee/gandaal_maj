<?php

namespace App\Form;

use App\Entity\ConfigActiviteScolaire;
use App\Entity\TarifActiviteScolaire;
use App\Repository\ConfigActiviteScolaireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TarifActiviteScolaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $builder
            ->add('activite', EntityType::class, [
                'class' => ConfigActiviteScolaire::class,
                "choice_label"  => 'nom',
                "placeholder" => "Selectionnez",
                "required" => true,
                "label" => "Activité*",
                'query_builder' => function (ConfigActiviteScolaireRepository $activiteRep) use ($etablissement) {
                    return $activiteRep->createQueryBuilder('a')
                        ->where('a.etablissement = :etablissement')
                        ->setParameter('etablissement', $etablissement)
                        ->orderBy('a.nom');
                },
            ])
            ->add('type', ChoiceType::class,[
                "choices"       =>  [
                    "annuel"           =>"annuel",
                    "mensuel"           =>"mensuel",
                ],
                "label"     =>"Type de paiement*",
                'required' => true
            ])

            
            ->add('montant', TextType::class, [
                'label' => 'Tarif*',
                "required"  =>false,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TarifActiviteScolaire::class,
            'etablissement' => Null,
        ]);
    }
}
