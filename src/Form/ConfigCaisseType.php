<?php

namespace App\Form;

use App\Entity\ConfigCaisse;
use App\Entity\Etablissement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ConfigCaisseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('type', ChoiceType::class,[
                "choices"       =>  [
                    "caisse"           =>"caisse",
                    "banque"           =>"banque",
                ],
                "required"  => true,
                "label"     =>"Type de caisse",
            ])
            ->add('numero')
            ->add('document', ChoiceType::class,[
                "choices"       =>  [
                    "actif"           =>"actif",
                    "inactif"           =>"inactif",
                ],
                "required"  => false,
                "label"     =>"Affichage sur les documents",
            ])
            // ->add('etablissement', EntityType::class, [
            //     'class' => Etablissement::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigCaisse::class,
        ]);
    }
}
