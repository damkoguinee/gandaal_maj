<?php

namespace App\Form;


use App\Entity\Recette;
use App\Entity\CategorieRecette;
use App\Entity\ConfigCaisse;
use App\Entity\ConfigDevise;
use App\Entity\ConfigModePaiement;
use App\Repository\ConfigCaisseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $builder
            ->add('categorie', EntityType::class, [
                "class"             => CategorieRecette::class,
                "choice_label"  =>  function(CategorieRecette $a){
                    return $a->getNom();
                },
                "placeholder"       =>"Selectionner une categorie",
                "required"  =>true,
                "label"     =>"Catégorie de la recette*"
            ])
            ->add('montant', TextType::class, [
                'label' => 'Montant*',
                "required"  =>false,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])

            ->add('tva', TextType::class, [
                'label' => 'Montant TVA',
                "required"  =>false,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
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

            ->add('devise', EntityType::class, [
                'class' => ConfigDevise::class,
                'choice_label' => 'nom',
                'label' => "Devise"
            ])

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
            ->add('modePaie', EntityType::class, [
                "class"             => ConfigModePaiement::class,
                "choice_label"  =>  function(ConfigModePaiement $a){
                    return $a->getNom();
                },
                "placeholder"       =>"Selectionner le mode de paie",
                "required"  =>true,
                "label"     =>"Mode de paiement*"
            ])
            ->add('description', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le commentaire ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  =>true,
                "label"     =>"Commentaire*"
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
            'data_class' => Recette::class,
            'etablissement' => null,
        ]);
    }
}
