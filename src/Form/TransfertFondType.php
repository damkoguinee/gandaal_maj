<?php

namespace App\Form;

use App\Entity\ConfigCaisse;
use App\Entity\ConfigDevise;
use App\Entity\ConfigModePaiement;
use App\Entity\TransfertFond;
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

class TransfertFondType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $transfert = $options["data"];
        $builder
            ->add('montant', TextType::class, [
                'label' => 'Montant à transférer*',
                "required"  =>false,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            ->add('devise', EntityType::class, [
                'class' => ConfigDevise::class,
                'choice_label' => 'nom',
                'label' => "Devise*"
            ])
            ->add('caisse', EntityType::class, [
                'class' => ConfigCaisse::class,
                'choice_label' =>  function(ConfigCaisse $a){
                    return $a->getNom();
                },
                'required' => false,
                'label' => 'Caisse de départ',
                'placeholder' => "Sélectionnez la caisse de départ",
                'query_builder' => function (ConfigCaisseRepository $er) use ($etablissement) {
                    return $er->createQueryBuilder('c')
                        ->where('c.etablissement = :etablissement')
                        ->setParameter('etablissement', $etablissement);

                },

            ])

            ->add('caisseReception', EntityType::class, [
                'class' => ConfigCaisse::class,
                'choice_label' =>  function(ConfigCaisse $a){
                    return $a->getNom();
                },
                'required' => false,
                'label' => 'Caisse de reception',
                'placeholder' => "Sélectionnez la caisse de reception",
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
            'data_class' => TransfertFond::class,
            'etablissement' => null,
        ]);
    }
}
