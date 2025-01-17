<?php

namespace App\Form;

use App\Entity\ConfigCaisse;
use App\Entity\ConfigModePaiement;
use App\Entity\ConfigurationLogiciel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ConfigurationLogicielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('color', ColorType::class, [
            'label' => 'Couleur facture',
            'required' => false,
            'attr' => [
                'placeholder' => 'Choisissez une couleur'
            ]
        ])
        ->add('backgroundColor', ColorType::class, [
            'label' => 'Couleur de Fond facture',
            'required' => false,
            'attr' => [
                'placeholder' => 'Choisissez une couleur de fond'
            ]
        ])

        ->add('documentEleve', ChoiceType::class, [
            'choices' => [
                'actif' => 'actif',
                'inactif' => 'inactif',
            ],
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Document élève'
        ])

        ->add('caisseDefaut', EntityType::class, [
            'class' => ConfigCaisse::class,
            'choice_label' => 'nom',
            "placeholder" => "Selectionnez une caisse",
            'label' => 'Caisse par défaut',
            'required' => false,


        ])
        ->add('modePaieDefaut', EntityType::class, [
            'class' => ConfigModePaiement::class,
            'choice_label' => 'nom',
            "placeholder" => "Selectionnez un mode de paie",
            'label' => 'Mode de paie par défaut',
            'required' => false,

        ])

        ->add('longLogo', null, [
            'label' => 'Longueur du logo',
            'required' => false,
        ])
        ->add('largLogo', null, [
            'label' => 'largeur du logo',
            'required' => false
        ] )

        ->add('formatBulletin', ChoiceType::class, [
            'choices' => [
                'format1' => 'format1',
                'format2' => 'format2',
            ],
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Format Bulletin'
        ])
        



        ->add('cheminSauvegarde', null, [
            'label' => 'Chemin sauvegarde en local',
            'required' => false,
        ])

        ->add('cheminMysql', null, [
            'label' => 'Chemin SQL sauvegarde',
            'required' => false,
        ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigurationLogiciel::class,
        ]);
    }
}
