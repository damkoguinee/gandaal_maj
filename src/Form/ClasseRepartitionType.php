<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Formation;
use App\Entity\ClasseRepartition;
use App\Entity\Enseignant;
use App\Entity\Personnel;
use App\Repository\EnseignantRepository;
use App\Repository\FormationRepository;
use App\Repository\PersonnelRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ClasseRepartitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formations = $options['formations'];
        $etablissement = $options['etablissement'];
        $builder
            ->add('nom', null, [
                "label"     => "Nom de la classe*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  150,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 150 caractères"
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide"])
                ]
            ])
            ->add('promo', ChoiceType::class, [
                'choices' => $options['year_choices'],
                'required' => true,
                'label' => 'Année Scolaire*'
            ])
            ->add('responsable', EntityType::class, [
                'class' => Enseignant::class,
                "choice_label"  =>  function(Enseignant $a){
                    return ucwords($a->getPrenom()). ' '.strtoupper($a->getNom());
                },
                "placeholder" => "Selectionner le responsable",

                'query_builder' => function (EnseignantRepository $EnseignantRepository) use ($etablissement) {
                    return $EnseignantRepository->createQueryBuilder('e')
                        ->where('e.etablissement = (:etablissement)')
                        ->setParameter('etablissement', $etablissement);
                },
            ])

            ->add('formation', EntityType::class, [
                'class' => Formation::class,
                "choice_label"  =>  function(Formation $a){
                    return $a->getCode();
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClasseRepartition::class,
            'year_choices' => [],
            'formations' => null,
            'etablissement' => NULL,
        ]);
    }
}
