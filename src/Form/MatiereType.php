<?php

namespace App\Form;

use App\Entity\Matiere;
use App\Entity\Formation;
use App\Entity\CategorieMatiere;
use App\Repository\FormationRepository;
use phpDocumentor\Reflection\PseudoTypes\False_;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MatiereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formations = $options['formations'];
        $builder
            ->add('nom', null, [
                "label"     => "Nom de la matiere*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  150,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 150 caractères"
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide"])
                ]
            ])
            ->add('codeMatiere', null, [
                "label"     => "Code de la matière*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  50,
                        "maxMessage"   =>  "Le code ne doit pas depassé 50 caractères"
                    ]),
                    new NotBlank(["message" => "le code ne peut pas être vide"])
                ]
            ])
            ->add('coef', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => 'Le Coef horaire doit être un nombre.',
                    ])
                ],
                // "attr"  =>["placeholder" =>"Entrer le poid"],
                "required"  =>false,
                "label"     =>"Coef"
            ])
            ->add('nombreHeure', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => "Le Nombre d'heure horaire doit être un nombre.",
                    ])
                ],
                "required"  =>false,
                "label"     =>"Nombre d'heure"
            ])
            ->add('formation', EntityType::class, [
                'class' => Formation::class,
                "choice_label"  =>  function(Formation $a){
                    return $a->getNom();
                },
                "placeholder" => "Selectionner la formation",
                "required" => true,
                "label" => "Formation*",
                'query_builder' => function (FormationRepository $formationRep) use ($formations) {
                    return $formationRep->createQueryBuilder('f')
                        ->where('f.id IN (:formations)')
                        ->setParameter('formations', $formations);
                },
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieMatiere::class,
                'choice_label' => 'nom',
            ])

            ->add('etatPedagogie', ChoiceType::class, [
                'label' => 'Etat pédagogie',
                'required' => False,
                'choices' => [
                    'actif' => 'actif',
                    'inactif' => 'inactif',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matiere::class,
            'formations' => null
        ]);
    }
}
