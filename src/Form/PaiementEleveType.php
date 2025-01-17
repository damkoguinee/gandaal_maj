<?php

namespace App\Form;

use App\Entity\ConfigCaisse;
use App\Entity\ConfigDevise;
use App\Entity\ConfigModePaiement;
use App\Entity\Eleve;
use App\Entity\Etablissement;
use App\Entity\PaiementEleve;
use App\Entity\User;
use App\Repository\ConfigCaisseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PaiementEleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $builder
            
            ->add('montant', TextType::class, [
                'label' => 'Montant Payé',
                "required"  => false,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            ->add('taux', NULL, [
                'data' => 1
            ])
            
            // ->add('typePaie')
        
            ->add('caisse', EntityType::class, [
                'class' => ConfigCaisse::class,
                'choice_label' => 'nom',
                'required' => true,
                'label' => 'Compte*',
                'placeholder' => "Sélectionnez un compte",
                'query_builder' => function (ConfigCaisseRepository $er) use ($etablissement) {
                    return $er->createQueryBuilder('c')
                        ->where('c.etablissement = :etablissement')
                        ->setParameter('etablissement', $etablissement);

                },

            ])
            ->add('devise', EntityType::class, [
                'class' => ConfigDevise::class,
                'choice_label' => 'nom',
                'label' => "Devise*",
                'required' => true,
            ])
            ->add('modePaie', EntityType::class, [
                'class' => ConfigModePaiement::class,
                'choice_label' => 'nom',
                'label' => 'Mode de paie*',
                'required' => true,
            ])

            ->add('banquePaie', null, [
                'label' => 'Banque paie',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 50,
                        'maxMessage'    => "La banque chèque ne doit pas depasser 50 caractères"
                    ])
                ]
            ])
            ->add('numeroPaie', null, [
                'label' => 'N°Chèque\Bord',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 20,
                        'maxMessage'    => "Le numéro chèque ne doit pas depasser 20 caractères"
                    ])
                ]
            ])

            ->add('datePaiement', DateType::class, [
                'label' => 'Date de paiement*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date par défaut sur la date du jour
                
                'attr' => ['max' => (new \DateTime())->format('Y-m-d')], // Limiter la sélection à la date d'aujourd'hui ou antérieure
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaiementEleve::class,
            'etablissement' => NULL
        ]);
    }
}
