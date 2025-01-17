<?php

namespace App\Form;

use App\Entity\PaiementSalairePersonnel;
use App\Entity\PaimentSalairePersonnel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaiementSalairePersonnelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('periode')
            // ->add('dateSaisie')
            // ->add('commentaires')
            // ->add('salaireBrut')
            // ->add('prime')
            // ->add('avanceSalaire')
            // ->add('cotisation')
            // ->add('salaireNet')
            // ->add('personnel')
            // ->add('saisiePar')
            // ->add('compteRetrait')
            // ->add('typePaiement')
            // ->add('devise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaiementSalairePersonnel::class,
        ]);
    }
}
