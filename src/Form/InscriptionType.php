<?php

namespace App\Form;

use App\Entity\ClasseRepartition;
use App\Entity\Eleve;
use App\Entity\Inscription;
use App\Entity\Personnel;
use App\Repository\ClasseRepartitionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $classe_defaut = $options['data']->getClasse();
        $builder
            ->add('classe', ChoiceType::class, [
                'choices' => $options['classes'],
                'placeholder' => 'Sélectionnez une classe',
                'required' => true,
                'label' => 'Classe*',
                'choice_label' => function ($classeRepartition) {
                    return $classeRepartition->getNom();
                },
                'choice_value' => function ($classeRepartition) {
                    return $classeRepartition ? $classeRepartition->getId() : '';
                },
                'data' => $classe_defaut, // Définir l'objet ClasseRepartition par défaut
            ])

            ->add('etatScol', ChoiceType::class,[
                "choices"       =>  [
                    "admis"           =>"admis",
                    "redoublant"           =>"redoublant",
                ],
                "label"     =>"Etat scolarité*",
                'required' => true
            ])

            ->add('statut', ChoiceType::class,[
                "choices"       =>  [
                    "actif"           =>"actif",
                    "inactif"           =>"inactif",
                ],
                "label"     =>"Statut*",
                'required' => true
            ])
            ->add('remiseInscription', NULL, [
                
                'label' => 'Remise Inscription',
            ])
            ->add('remiseScolarite', NULL, [
                
                'label' => 'Remise Scolarité',
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
            'classes' => []
        ]);
    }
}
