<?php

namespace App\Form;

use App\Entity\Eleve;
use App\Entity\LienFamilial;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LienFamilialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lienParent')
            ->add('eleve1', EntityType::class, [
                'class' => Eleve::class,
                'choice_label' => 'id',
            ])
            ->add('eleve2', EntityType::class, [
                'class' => Eleve::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LienFamilial::class,
        ]);
    }
}
