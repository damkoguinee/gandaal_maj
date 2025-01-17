<?php

namespace App\Form;

use App\Entity\Eleve;
use App\Entity\Etablissement;
use App\Entity\Tuteur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TuteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('username')
            // ->add('roles')
            // ->add('password')
            ->add('nom')
            ->add('prenom')
            // ->add('email')
            // ->add('telephone')
            // ->add('adresse')
            // ->add('ville')
            // ->add('pays')
            // ->add('matricule')
            // ->add('sexe')
            // ->add('dateNaissance', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('lieuNaissance')
            // ->add('statut')
            // ->add('photo')
            // ->add('typeUser')
            // ->add('lienFamilial')
            // ->add('profession')
            // ->add('lieuTravail')
            
            // ->add('eleve', EntityType::class, [
            //     'class' => Eleve::class,
            //     'choice_label' => function (Eleve $a) {
            //         return ucwords($a->getPrenom())." ".strtoupper($a->getNom());
            //     },
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tuteur::class,
        ]);
    }
}
