<?php

namespace App\Form;

use App\Entity\FonctionnementScolaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FonctionnementScolaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', ChoiceType::class,[
                "choices"       =>  [
                    "1er trimestre"           =>"1er trimestre",
                    "2ème trimestre"           =>"2ème trimestre",
                    "3ème trimestre"           =>"3ème trimestre",
                    "1er semestre"           =>"1er semestre",
                    "2ème semestre"           =>"2ème semestre",
                ],
                "label"     =>"Trimestre/Semestre*",
                'required' => true
            ])
            ->add('dateDebut', null, [
                'widget' => 'single_text',
                'label' => 'Date de début*',

            ])
            ->add('dateFin', null, [
                'widget' => 'single_text',
                'label' => 'Date de fin*',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FonctionnementScolaire::class,
        ]);
    }
}
