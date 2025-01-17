<?php

namespace App\Form;

use App\Entity\Versement;
use App\Entity\ConfigCaisse;
use App\Entity\ConfigDevise;
use App\Entity\ConfigModePaiement;
use App\Repository\ConfigCaisseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class VersementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $type1 = 'client';
        $type2 = 'client-fournisseur';
        $versement = $options["data"];
        $builder
            ->add('montant', TextType::class, [
                'label' => 'Montant versé*',
                "required"  =>false,
                'attr' => [
                    'onkeyup' => "formatMontant(this)",
                    'style' => 'font-size: 20px; font-weight: bold; ',
                ]
            ])
            ->add('taux', NumberType::class, [
                'label' => 'Taux',
                'data' => $versement->getTaux() ?? 1,
                'scale' => 2,
                "required"  =>true,
            ])
            ->add('devise', EntityType::class, [
                'class' => ConfigDevise::class,
                'choice_label' => 'nom',
                'label' => "Devise"
            ])
            ->add('modePaie', EntityType::class, [
                'class' => ConfigModePaiement::class,
                'choice_label' => 'nom',
                'label' => 'Mode de paie*',
                'placeholder' => "Sélectionnez",
                'required' => true,
            ])
            ->add('numeroPaie', null, [
                'label' => 'N°Chèque\Bord',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "Le numéro chèque ne doit pas depasser 100 caractères"
                    ])
                ]
            ])
            ->add('banquePaie', null, [
                'label' => 'Banque Chèque',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "La banque chèque ne doit pas depasser 100 caractères"
                    ])
                ]
            ])
            ->add('caisse', EntityType::class, [
                'class' => ConfigCaisse::class,
                'choice_label' => 'nom',
                'required' => true,
                'label' => 'Compte de dépôt*',
                'placeholder' => "Sélectionnez un compte",
                'query_builder' => function (ConfigCaisseRepository $er) use ($etablissement) {
                    return $er->createQueryBuilder('c')
                        ->where('c.etablissement = :etablissement')
                        ->setParameter('etablissement', $etablissement);

                },

            ])
            ->add('description', null, [
                'label' => 'description*',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 255,
                        'maxMessage'    => "La description ne doit pas depasser 255 caractères"
                    ])
                ]
            ])

            ->add('dateOperation', DateTimeType::class, [
                'label' => 'Date opération*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d\TH:i'), // Limiter la sélection à la date et l'heure actuelles ou antérieures
                ],
                'html5' => true, // Pour activer le support HTML5
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Versement::class,
            'etablissement' => null,
        ]);
    }
}
