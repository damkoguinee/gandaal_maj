<?php

namespace App\Form;

use App\Entity\AvanceSalairePersonnel;
use App\Entity\PersonnelActif;
use App\Entity\PrimePersonnel;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvanceSalairePersonnelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant')
            ->add('periode')
            // ->add('personnel', EntityType::class, [
            //     'class' => PersonnelActif::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvanceSalairePersonnel::class,
        ]);
    }
}
