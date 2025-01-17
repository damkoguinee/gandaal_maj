<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Matiere;
use App\Entity\DevoirEleve;
use App\Entity\ClasseRepartition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DevoirEleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('typeDevoir', ChoiceType::class,[
                "choices"       =>  [
                    "note de cours"           =>"note de cours",
                    "composition"           =>"composition",
                ],
                "required"  => true,
                "label"     =>"Type de devoir*",
            ])
            ->add('coef')

            ->add('periode', ChoiceType::class,[
                "choices"       =>  [
                    "1er"           =>"1",
                    "2ème"           =>"2",
                    "3ème"           =>"3",
                ],
                "required"  => true,
                "label"     =>"Période*",
            ])

            

            ->add('dateDevoir', null, [
                'widget' => 'single_text',
            ])
            
            ->add('classe', EntityType::class, [
                'class' => ClasseRepartition::class,
                'choice_label' => 'nom',
            ])
            ->add('matiere', EntityType::class, [
                'class' => Matiere::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DevoirEleve::class,
        ]);
    }
}
