<?php

namespace App\Form;

use App\Entity\Eleve;
use App\Entity\Etablissement;
use App\Entity\InscriptionActivite;
use App\Entity\TarifActiviteScolaire;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('remise', Null, [
                'data' => 0,
                'label' => 'Remise Activité*',
                'required' => true,
            ])
            ->add('dateInscription', DateType::class, [
                'label' => "Date d'inscription*",
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date par défaut sur la date du jour
                
                'attr' => ['max' => (new \DateTime())->format('Y-m-d')], // Limiter la sélection à la date d'aujourd'hui ou antérieure
            ])
            // ->add('eleve', EntityType::class, [
            //     'class' => Eleve::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('tarifActivite', EntityType::class, [
            //     'class' => TarifActiviteScolaire::class,
            //     'choice_label' => function (TarifActiviteScolaire $a) {
            //         return ucwords($a->getActivite()->getNom())." ".number_format($a->getMontant(),0,',',' ').' '.ucwords($a->getType());
            //     },
            //     'multiple' => true,
            //     'label' => 'Activités*',
            //     'required' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InscriptionActivite::class,
        ]);
    }
}
