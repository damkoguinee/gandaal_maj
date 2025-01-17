<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Client;
use App\Entity\Devise;
use App\Entity\etablissementxVentes;
use App\Entity\Decaissement;
use App\Entity\ModePaiement;
use App\Entity\PointDeVente;
use App\Repository\UserRepository;
use App\Repository\CaisseRepository;
use App\Entity\CategorieDecaissement;
use App\Entity\ConfigCaisse;
use App\Entity\ConfigDevise;
use App\Entity\ConfigModePaiement;
use App\Repository\ConfigCaisseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class DecaissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $type1 = 'client';
        $type2 = 'client-fournisseur';
        $builder
            ->add('montant', TextType::class, [
                'label' => 'Montant décaissé*',
                "required"  =>false,
                    'attr' => [
                        'placeholder' => '1 000 000',
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
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
                'label' => 'Compte à Prélever*',
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
                        'maxMessage'    => "Le description ne doit pas depasser 255 caractères"
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

            ->add('document', FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "application/pdf", "image/jpeg", "image/gif", "image/png" ],
                        "mimeTypesMessage" => "Format accepté : PDF, gif, jpg, png",
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                'label' =>"Joindre un document",
                "help" => "Formats autorisés : PDF, gif, jpg, png"
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Decaissement::class,
            'etablissement' => null,
        ]);
    }
}
