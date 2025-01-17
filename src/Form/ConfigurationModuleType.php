<?php

namespace App\Form;

use App\Entity\Cursus;
use App\Entity\ConfigurationModule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ConfigurationModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('periode', ChoiceType::class, [
                "choices" => [
                    "Janvier" => "01",
                    "Février" => "02",
                    "Mars" => "03",
                    "Avril" => "04",
                    "Mai" => "05",
                    "Juin" => "06",
                    "Juillet" => "07",
                    "Août" => "08",
                    "Septembre" => "09",
                    "Octobre" => "10",
                    "Novembre" => "11",
                    "Décembre" => "12",
                ],
                "required" => true,
                "label" => "Période*",
            ])
            
           
            ->add('cursus', EntityType::class, [
                'class' => Cursus::class,
                'choice_label' => 'nom',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigurationModule::class,
        ]);
    }
}
