<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\FraisScolarite;
use App\Entity\TranchePaiement;
use App\Repository\TranchePaiementRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class FraisScolariteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $etablissement = $options['etablissement'];
        $promo = $options['promo'];
        $builder
            // ->add('formation', EntityType::class, [
            //     'class' => Formation::class,
            //     'choice_label' => function(Formation $a) {
            //         return $a->getCode();
            //     },
            //     'placeholder' => 'Sélectionner les formations',
            //     'required' => true,
            //     'label' => 'Formations*',
            //     'query_builder' => function (FormationRepository $formationRep) use ($formations) {
            //         return $formationRep->createQueryBuilder('f')
            //             ->where('f.id IN (:formations)')
            //             ->setParameter('formations', $formations);
            //     },
            //     'multiple' => true, // Permettre la sélection multiple
            //     'expanded' => false, // Afficher sous forme de liste déroulante (changer à true pour des cases à cocher)
            // ])

            // ->add('formation', EntityType::class, [
            //     'class' => Formation::class,
            //     'choice_label' => 'nom',
            //     'label' => 'Formation*'
            // ])

            ->add('tranche', EntityType::class, [
                'class' => TranchePaiement::class,
                "choice_label"  =>  "nom",
                "placeholder" => "Selectionner la tranche",
                "required" => true,
                "label" => "Tranche*",
                'query_builder' => function (TranchePaiementRepository $tranchePaiementRepository) use ($promo, $etablissement) {
                    return $tranchePaiementRepository->createQueryBuilder('t')
                    ->andWhere('t.promo = :promo')
                    ->andWhere('t.etablissement = :etablissement')
                    ->setParameter('promo', $promo)
                    ->setParameter('etablissement', $etablissement);
                },
            ])
            ->add('montant', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => 'Le montant de base horaire doit être un nombre.',
                    ])
                ],
                // "attr"  =>["placeholder" =>"Entrer le poid"],
                "required"  => true,
                "label"     =>"Montant*"
            ])
            ->add('dateLimite', null, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date limite*'
            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FraisScolarite::class,
            'etablissement' => [],
            'promo' => Null,
        ]);
    }
}
