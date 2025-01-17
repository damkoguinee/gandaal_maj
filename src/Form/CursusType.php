<?php

namespace App\Form;

use App\Entity\Cursus;
use App\Entity\Etablissement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CursusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('fonctionnement', ChoiceType::class,[
                "choices"       =>  [
                    "trimestre"           =>"trimestre",
                    "semestre"           =>"semestre",
                ],
                "label"     =>"Fonctionnement*",
                'required' => true
            ])
            // ->add('etablissement', EntityType::class, [
            //     'class' => Etablissement::class,
            //     'choice_label' => 'nom',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cursus::class,
        ]);
    }
}
