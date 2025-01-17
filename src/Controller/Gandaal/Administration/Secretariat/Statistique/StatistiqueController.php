<?php

namespace App\Controller\Gandaal\Administration\Secretariat\Statistique;

use App\Entity\ClasseRepartition;
use App\Entity\Cursus;
use App\Entity\Etablissement;
use App\Entity\Formation;
use App\Repository\ClasseRepartitionRepository;
use App\Repository\CursusRepository;
use App\Repository\FormationRepository;
use App\Repository\InscriptionRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/gandaal/administration/secretariat/statistique')]
class StatistiqueController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_index', methods: ['GET'])]
    public function index(Etablissement $etablissement, InscriptionRepository $inscriptionRep, CursusRepository $cursusRep, Request $request, SessionInterface $session): Response
    {  
        if ($session->get('session_cursus')) {
            $cursus = $cursusRep->findBy(['id' =>$session->get('session_cursus')]);
        }else{

            $cursus = $cursusRep->findBy(['etablissement' => $etablissement]);
        }

        // Initialisation des variables pour les statistiques par cursus
        $statistiques = [];

        $totalFilles = 0;
        $totalGarcons = 0;
        $totalElevesGlobal = 0;
        $totalAnciens = 0;
        $totalNouveaux = 0;
        $totalRedoublants = 0;
        $totalAbandons = 0;

        foreach ($cursus as $item) {
            // Récupérer les inscriptions pour le cursus actuel
            $inscriptions = $inscriptionRep->listeDesElevesInscritsParCursus($item, $session->get('promo'));

            // Compteurs pour ce cursus
            $effectifFilles = 0;
            $effectifGarcons = 0;
            $totalEleves = 0;
            $anciens = 0;
            $nouveaux = 0;
            $abandons = 0;
            $redoublants = 0;

            foreach ($inscriptions as $inscription) {
                if ($inscription->getStatut() != 'inactif') {
                    # code...
                    $eleve = $inscription->getEleve();
                    $sexe = $eleve->getSexe();
    
                    if ($sexe == 'f') {
                        $effectifFilles++;
                    } elseif ($sexe == 'm') {
                        $effectifGarcons++;
                    }
    
                    if ($inscription->getType() === 'inscription') {
                        $nouveaux++;
                    } else {
                        $anciens++;
                    }
    
                    if ($inscription->getEtatScol() == 'redoublant') {
                        $redoublants++;
                    }
                    $totalEleves++;
                }
                // dd($inscription->getStatut());
                if ($inscription->getStatut() == 'inactif') {
                    $abandons++;
                }
                


            }

            // Calculer les pourcentages
            $pourcentageFilles = $totalEleves > 0 ? ($effectifFilles / $totalEleves) * 100 : 0;
            $pourcentageGarcons = $totalEleves > 0 ? ($effectifGarcons / $totalEleves) * 100 : 0;
            $pourcentageNouveaux = $totalEleves > 0 ? ($nouveaux / $totalEleves) * 100 : 0;

            // Accumuler les totaux globaux
            $totalFilles += $effectifFilles;
            $totalGarcons += $effectifGarcons;
            $totalElevesGlobal += $totalEleves;
            $totalAnciens += $anciens;
            $totalNouveaux += $nouveaux;
            $totalRedoublants += $redoublants;
            $totalAbandons += $abandons;

            // Stocker les statistiques pour chaque cursus
            $statistiques[] = [
                'cursus' => $item,
                'effectifFilles' => $effectifFilles,
                'pourcentageFilles' => $pourcentageFilles,
                'effectifGarcons' => $effectifGarcons,
                'pourcentageGarcons' => $pourcentageGarcons,
                'totalEleves' => $totalEleves,
                'anciens' => $anciens,
                'nouveaux' => $nouveaux,
                'redoublants' => $redoublants,
                'abandons' => $abandons,
                'pourcentageNouveaux' => $pourcentageNouveaux,
            ];
        }
        

        // Transmettre les totaux à la vue
        return $this->render('gandaal/administration/secretariat/statistique/index.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiques,
            'totalFilles' => $totalFilles,
            'totalGarcons' => $totalGarcons,
            'totalElevesGlobal' => $totalElevesGlobal,
            'totalAnciens' => $totalAnciens,
            'totalNouveaux' => $totalNouveaux,
            'totalRedoublants' => $totalRedoublants,
            'totalAbandons' => $totalAbandons,
            'promo' => $session->get('promo'),

        ]);
    }

    #[Route('/niveau/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_niveau', methods: ['GET'])]
    public function statistiqueNiveau(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep, 
        FormationRepository $formationRep, 
        CursusRepository $cursusRep, 
        ClasseRepartitionRepository $classeRep, 
        Request $request, 
        SessionInterface $session
    ): Response {  
        // Initialisation des variables pour les statistiques par classe
        $statistiquesParClasse = [];

        // Récupérer les classes pour l'établissement et la promo
        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));
        }else{
            $classes = $classeRep->listeDesClassesParEtablissementParPromo($etablissement, $session->get('promo'));
        }

        foreach ($classes as $classe) {
            $classeFormation = $classe->getFormation()->getClasse();  // Classe associée à la formation

            // Initialiser les statistiques pour cette classe si nécessaire
            if (!isset($statistiquesParClasse[$classeFormation])) {
                $statistiquesParClasse[$classeFormation] = [
                    'effectifFilles' => 0,
                    'effectifGarcons' => 0,
                    'totalEleves' => 0,
                    'anciens' => 0,
                    'nouveaux' => 0,
                    'abandons' => 0,
                    'redoublants' => 0,
                ];
            }

            // Récupérer les inscriptions pour la classe
            $inscriptions = $inscriptionRep->findBy(['classe' => $classe]);
            foreach ($inscriptions as $inscription) {
                if ($inscription->getStatut() != 'inactif') {
                    $eleve = $inscription->getEleve();
                    $sexe = $eleve->getSexe();

                    // Compter filles et garçons
                    if ($sexe == 'f') {
                        $statistiquesParClasse[$classeFormation]['effectifFilles']++;
                    } elseif ($sexe == 'm') {
                        $statistiquesParClasse[$classeFormation]['effectifGarcons']++;
                    }

                    // Compter nouveaux et anciens
                    if ($inscription->getType() == 'inscription') {
                        $statistiquesParClasse[$classeFormation]['nouveaux']++;
                    } else {
                        $statistiquesParClasse[$classeFormation]['anciens']++;
                    }

                    // Compter redoublants
                    if ($inscription->getEtatScol() == 'redoublant') {
                        $statistiquesParClasse[$classeFormation]['redoublants']++;
                    }

                    // Incrémenter le nombre total d'élèves pour cette classe
                    $statistiquesParClasse[$classeFormation]['totalEleves']++;
                }

                // Compter les abandons (statut inactif)
                if ($inscription->getStatut() == 'inactif') {
                    $statistiquesParClasse[$classeFormation]['abandons']++;
                }
            }
       

            // Calcul des pourcentages pour cette classe
            $totalElevesClasse = $statistiquesParClasse[$classeFormation]['totalEleves'];
            $statistiquesParClasse[$classeFormation]['pourcentageFilles'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['effectifFilles'] / $totalElevesClasse) * 100
                : 0;

            $statistiquesParClasse[$classeFormation]['pourcentageGarcons'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['effectifGarcons'] / $totalElevesClasse) * 100
                : 0;

            $statistiquesParClasse[$classeFormation]['pourcentageNouveaux'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['nouveaux'] / $totalElevesClasse) * 100
                : 0;
        }
        // dd($statistiquesParClasse);
        // Renvoyer les statistiques à la vue
        return $this->render('gandaal/administration/secretariat/statistique/statistique_niveau.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiquesParClasse,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/formation/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_formation', methods: ['GET'])]
    public function statistiqueFormation(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep, 
        FormationRepository $formationRep, 
        CursusRepository $cursusRep, 
        ClasseRepartitionRepository $classeRep, 
        Request $request, 
        SessionInterface $session
    ): Response {  
        // Initialisation des variables pour les statistiques par classe
        $statistiquesParClasse = [];

        // Récupérer les classes pour l'établissement et la promo

        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));
        }else{
            $classes = $classeRep->listeDesClassesParEtablissementParPromo($etablissement, $session->get('promo'));
        }

        foreach ($classes as $classe) {
            $classeFormation = $classe->getFormation()->getNom();  // Classe associée à la formation

            // Initialiser les statistiques pour cette classe si nécessaire
            if (!isset($statistiquesParClasse[$classeFormation])) {
                $statistiquesParClasse[$classeFormation] = [
                    'effectifFilles' => 0,
                    'effectifGarcons' => 0,
                    'totalEleves' => 0,
                    'anciens' => 0,
                    'nouveaux' => 0,
                    'abandons' => 0,
                    'redoublants' => 0,
                    'formation' => $classe->getFormation() 
                ];
            }

            // Récupérer les inscriptions pour la classe
            $inscriptions = $inscriptionRep->findBy(['classe' => $classe]);
            foreach ($inscriptions as $inscription) {
                if ($inscription->getStatut() != 'inactif') {
                    $eleve = $inscription->getEleve();
                    $sexe = $eleve->getSexe();

                    // Compter filles et garçons
                    if ($sexe == 'f') {
                        $statistiquesParClasse[$classeFormation]['effectifFilles']++;
                    } elseif ($sexe == 'm') {
                        $statistiquesParClasse[$classeFormation]['effectifGarcons']++;
                    }

                    // Compter nouveaux et anciens
                    if ($inscription->getType() == 'inscription') {
                        $statistiquesParClasse[$classeFormation]['nouveaux']++;
                    } else {
                        $statistiquesParClasse[$classeFormation]['anciens']++;
                    }

                    // Compter redoublants
                    if ($inscription->getEtatScol() == 'redoublant') {
                        $statistiquesParClasse[$classeFormation]['redoublants']++;
                    }

                    // Incrémenter le nombre total d'élèves pour cette classe
                    $statistiquesParClasse[$classeFormation]['totalEleves']++;
                }

                // Compter les abandons (statut inactif)
                if ($inscription->getStatut() == 'inactif') {
                    $statistiquesParClasse[$classeFormation]['abandons']++;
                }
            }
       

            // Calcul des pourcentages pour cette classe
            $totalElevesClasse = $statistiquesParClasse[$classeFormation]['totalEleves'];
            $statistiquesParClasse[$classeFormation]['pourcentageFilles'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['effectifFilles'] / $totalElevesClasse) * 100
                : 0;

            $statistiquesParClasse[$classeFormation]['pourcentageGarcons'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['effectifGarcons'] / $totalElevesClasse) * 100
                : 0;

            $statistiquesParClasse[$classeFormation]['pourcentageNouveaux'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['nouveaux'] / $totalElevesClasse) * 100
                : 0;
        }
        // dd($statistiquesParClasse);
        // Renvoyer les statistiques à la vue
        return $this->render('gandaal/administration/secretariat/statistique/statistique_formation.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiquesParClasse,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/classe/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_classe', methods: ['GET'])]
    public function statistiqueClasse(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep, 
        FormationRepository $formationRep, 
        CursusRepository $cursusRep, 
        ClasseRepartitionRepository $classeRep, 
        Request $request, 
        SessionInterface $session
    ): Response {  
        // Initialisation des variables pour les statistiques par classe
        $statistiquesParClasse = [];

        // Récupérer les classes pour l'établissement et la promo
        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));
        }else{
            $classes = $classeRep->listeDesClassesParEtablissementParPromo($etablissement, $session->get('promo'));
        }

        foreach ($classes as $classe) {
            $classeFormation = $classe->getNom();  // Classe associée à la formation

            // Initialiser les statistiques pour cette classe si nécessaire
            if (!isset($statistiquesParClasse[$classeFormation])) {
                $statistiquesParClasse[$classeFormation] = [
                    'effectifFilles' => 0,
                    'effectifGarcons' => 0,
                    'totalEleves' => 0,
                    'anciens' => 0,
                    'nouveaux' => 0,
                    'abandons' => 0,
                    'redoublants' => 0,
                    'classe' => $classe
                ];
            }

            // Récupérer les inscriptions pour la classe
            $inscriptions = $inscriptionRep->findBy(['classe' => $classe]);
            foreach ($inscriptions as $inscription) {
                if ($inscription->getStatut() != 'inactif') {
                    $eleve = $inscription->getEleve();
                    $sexe = $eleve->getSexe();

                    // Compter filles et garçons
                    if ($sexe == 'f') {
                        $statistiquesParClasse[$classeFormation]['effectifFilles']++;
                    } elseif ($sexe == 'm') {
                        $statistiquesParClasse[$classeFormation]['effectifGarcons']++;
                    }

                    // Compter nouveaux et anciens
                    if ($inscription->getType() == 'inscription') {
                        $statistiquesParClasse[$classeFormation]['nouveaux']++;
                    } else {
                        $statistiquesParClasse[$classeFormation]['anciens']++;
                    }

                    // Compter redoublants
                    if ($inscription->getEtatScol() == 'redoublant') {
                        $statistiquesParClasse[$classeFormation]['redoublants']++;
                    }

                    // Incrémenter le nombre total d'élèves pour cette classe
                    $statistiquesParClasse[$classeFormation]['totalEleves']++;
                }

                // Compter les abandons (statut inactif)
                if ($inscription->getStatut() == 'inactif') {
                    $statistiquesParClasse[$classeFormation]['abandons']++;
                }
            }
       

            // Calcul des pourcentages pour cette classe
            $totalElevesClasse = $statistiquesParClasse[$classeFormation]['totalEleves'];
            $statistiquesParClasse[$classeFormation]['pourcentageFilles'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['effectifFilles'] / $totalElevesClasse) * 100
                : 0;

            $statistiquesParClasse[$classeFormation]['pourcentageGarcons'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['effectifGarcons'] / $totalElevesClasse) * 100
                : 0;

            $statistiquesParClasse[$classeFormation]['pourcentageNouveaux'] = $totalElevesClasse > 0
                ? ($statistiquesParClasse[$classeFormation]['nouveaux'] / $totalElevesClasse) * 100
                : 0;
        }
        // dd($statistiquesParClasse);
        // Renvoyer les statistiques à la vue
        return $this->render('gandaal/administration/secretariat/statistique/statistique_classe.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiquesParClasse,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/cursus/show/{etablissement}/{cursus}', name: 'app_gandaal_administration_secretaraiat_statistique_cursus_show', methods: ['GET'])]
    public function statistiqueCursusShow(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep, 
        FormationRepository $formationRep, 
        CursusRepository $cursusRep, 
        ClasseRepartitionRepository $classeRep, 
        Request $request, 
        Cursus $cursus,
        SessionInterface $session
    ): Response {  

        // Initialisation des variables pour les statistiques par cursus
        $statistiques = [];
        
        // Récupérer les inscriptions pour le cursus actuel
        $inscriptions = $inscriptionRep->listeDesElevesInscritsParCursus($cursus, $session->get('promo'));

        // Compteurs pour ce cursus
        $effectifFilles = 0;
        $effectifGarcons = 0;
        $totalEleves = 0;
        $anciens = 0;
        $nouveaux = 0;
        $abandons = 0;
        $redoublants = 0;

        foreach ($inscriptions as $inscription) {
            if ($inscription->getStatut() != 'inactif') {
                # code...
                $eleve = $inscription->getEleve();
                $sexe = $eleve->getSexe();

                if ($sexe == 'f') {
                    $effectifFilles++;
                } elseif ($sexe == 'm') {
                    $effectifGarcons++;
                }

                if ($inscription->getType() === 'inscription') {
                    $nouveaux++;
                } else {
                    $anciens++;
                }

                if ($inscription->getEtatScol() == 'redoublant') {
                    $redoublants++;
                }
                $totalEleves++;
            }
            // dd($inscription->getStatut());
            if ($inscription->getStatut() == 'inactif') {
                $abandons++;
            }
        }

        // Calculer les pourcentages
        $pourcentageFilles = $totalEleves > 0 ? ($effectifFilles / $totalEleves) * 100 : 0;
        $pourcentageGarcons = $totalEleves > 0 ? ($effectifGarcons / $totalEleves) * 100 : 0;
        $pourcentageNouveaux = $totalEleves > 0 ? ($nouveaux / $totalEleves) * 100 : 0;

        $inscriptionsGroupByType = [];

        foreach ($inscriptions as $inscription) {
            $type = $inscription->getType();
            
            // Si le type n'est pas encore dans le tableau, l'initialiser
            if (!isset($inscriptionsGroupByType[$type])) {
                $inscriptionsGroupByType[$type] = [];
            }

            // Ajouter l'inscription au tableau du type correspondant
            $inscriptionsGroupByType[$type][] = $inscription;
        }


        // Stocker les statistiques pour chaque cursus
        $statistiques[] = [
            'cursus' => $cursus,
            'effectifFilles' => $effectifFilles,
            'pourcentageFilles' => $pourcentageFilles,
            'effectifGarcons' => $effectifGarcons,
            'pourcentageGarcons' => $pourcentageGarcons,
            'totalEleves' => $totalEleves,
            'anciens' => $anciens,
            'nouveaux' => $nouveaux,
            'redoublants' => $redoublants,
            'abandons' => $abandons,
            'pourcentageNouveaux' => $pourcentageNouveaux,
        ];
        
       
        return $this->render('gandaal/administration/secretariat/statistique/statistique_cursus_show.html.twig', [
            'etablissement' => $etablissement,
            'inscriptions' => $inscriptions,
            'statistiques' => $statistiques,
            'inscriptionsGroupByType' => $inscriptionsGroupByType,
            'cursus' => $cursus,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/niveau/show/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_niveau_show', methods: ['GET'])]
    public function statistiqueNiveauShow(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep, 
        FormationRepository $formationRep, 
        CursusRepository $cursusRep, 
        ClasseRepartitionRepository $classeRep, 
        Request $request, 
        SessionInterface $session
    ): Response {

        $statistiquesParClasse = [];

        
        $niveau = $request->get('niveau');  // Classe associée à la formation
        // Initialiser les statistiques pour cette classe si nécessaire
        if (!isset($statistiquesParClasse[$niveau])) {
            $statistiquesParClasse[$niveau] = [
                'effectifFilles' => 0,
                'effectifGarcons' => 0,
                'totalEleves' => 0,
                'anciens' => 0,
                'nouveaux' => 0,
                'abandons' => 0,
                'redoublants' => 0
            ];
        }

        // Récupérer les inscriptions pour la classe
        $inscriptions = $inscriptionRep->listeGeneraleDesElevesInscritsParNiveau($niveau, $session->get('promo'), $etablissement);

        $inscriptionsGroupByType = [];

        foreach ($inscriptions as $inscription) {
            $type = $inscription->getType();
            
            // Si le type n'est pas encore dans le tableau, l'initialiser
            if (!isset($inscriptionsGroupByType[$type])) {
                $inscriptionsGroupByType[$type] = [];
            }

            // Ajouter l'inscription au tableau du type correspondant
            $inscriptionsGroupByType[$type][] = $inscription;
        }

            
        foreach ($inscriptions as $inscription) {
            if ($inscription->getStatut() != 'inactif') {
                $eleve = $inscription->getEleve();
                $sexe = $eleve->getSexe();

                // Compter filles et garçons
                if ($sexe == 'f') {
                    $statistiquesParClasse[$niveau]['effectifFilles']++;
                } elseif ($sexe == 'm') {
                    $statistiquesParClasse[$niveau]['effectifGarcons']++;
                }

                // Compter nouveaux et anciens
                if ($inscription->getType() == 'inscription') {
                    $statistiquesParClasse[$niveau]['nouveaux']++;
                } else {
                    $statistiquesParClasse[$niveau]['anciens']++;
                }

                // Compter redoublants
                if ($inscription->getEtatScol() == 'redoublant') {
                    $statistiquesParClasse[$niveau]['redoublants']++;
                }

                // Incrémenter le nombre total d'élèves pour cette classe
                $statistiquesParClasse[$niveau]['totalEleves']++;
            }

            // Compter les abandons (statut inactif)
            if ($inscription->getStatut() == 'inactif') {
                $statistiquesParClasse[$niveau]['abandons']++;
            }
        }
        // Calcul des pourcentages pour cette classe
        $totalElevesClasse = $statistiquesParClasse[$niveau]['totalEleves'];
        $statistiquesParClasse[$niveau]['pourcentageFilles'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$niveau]['effectifFilles'] / $totalElevesClasse) * 100
            : 0;

        $statistiquesParClasse[$niveau]['pourcentageGarcons'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$niveau]['effectifGarcons'] / $totalElevesClasse) * 100
            : 0;

        $statistiquesParClasse[$niveau]['pourcentageNouveaux'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$niveau]['nouveaux'] / $totalElevesClasse) * 100
            : 0;
       
        return $this->render('gandaal/administration/secretariat/statistique/statistique_niveau_show.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiquesParClasse,
            'inscriptionsGroupByType' => $inscriptionsGroupByType,
            'niveau' => $niveau,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/formation/show/{etablissement}/{formation}', name: 'app_gandaal_administration_secretaraiat_statistique_formation_show', methods: ['GET'])]
    public function statistiqueFormationShow(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep, 
        FormationRepository $formationRep, 
        CursusRepository $cursusRep, 
        ClasseRepartitionRepository $classeRep, 
        Formation $formation,
        Request $request, 
        SessionInterface $session
    ): Response {

        $statistiquesParClasse = [];

        // Initialiser les statistiques pour cette classe si nécessaire
        if (!isset($statistiquesParClasse[$formation->getNom()])) {
            $statistiquesParClasse[$formation->getNom()] = [
                'effectifFilles' => 0,
                'effectifGarcons' => 0,
                'totalEleves' => 0,
                'anciens' => 0,
                'nouveaux' => 0,
                'abandons' => 0,
                'redoublants' => 0
            ];
        }

        // Récupérer les inscriptions pour la classe
        $inscriptions = $inscriptionRep->listeGeneraleDesElevesInscritsParFormation($formation, $session->get('promo'), $etablissement);

        $inscriptionsGroupByType = [];

        foreach ($inscriptions as $inscription) {
            $type = $inscription->getType();
            
            // Si le type n'est pas encore dans le tableau, l'initialiser
            if (!isset($inscriptionsGroupByType[$type])) {
                $inscriptionsGroupByType[$type] = [];
            }

            // Ajouter l'inscription au tableau du type correspondant
            $inscriptionsGroupByType[$type][] = $inscription;
        }

            
        foreach ($inscriptions as $inscription) {
            if ($inscription->getStatut() != 'inactif') {
                $eleve = $inscription->getEleve();
                $sexe = $eleve->getSexe();

                // Compter filles et garçons
                if ($sexe == 'f') {
                    $statistiquesParClasse[$formation->getNom()]['effectifFilles']++;
                } elseif ($sexe == 'm') {
                    $statistiquesParClasse[$formation->getNom()]['effectifGarcons']++;
                }

                // Compter nouveaux et anciens
                if ($inscription->getType() == 'inscription') {
                    $statistiquesParClasse[$formation->getNom()]['nouveaux']++;
                } else {
                    $statistiquesParClasse[$formation->getNom()]['anciens']++;
                }

                // Compter redoublants
                if ($inscription->getEtatScol() == 'redoublant') {
                    $statistiquesParClasse[$formation->getNom()]['redoublants']++;
                }

                // Incrémenter le nombre total d'élèves pour cette classe
                $statistiquesParClasse[$formation->getNom()]['totalEleves']++;
            }

            // Compter les abandons (statut inactif)
            if ($inscription->getStatut() == 'inactif') {
                $statistiquesParClasse[$formation->getNom()]['abandons']++;
            }
        }
        // Calcul des pourcentages pour cette classe
        $totalElevesClasse = $statistiquesParClasse[$formation->getNom()]['totalEleves'];
        $statistiquesParClasse[$formation->getNom()]['pourcentageFilles'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$formation->getNom()]['effectifFilles'] / $totalElevesClasse) * 100
            : 0;

        $statistiquesParClasse[$formation->getNom()]['pourcentageGarcons'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$formation->getNom()]['effectifGarcons'] / $totalElevesClasse) * 100
            : 0;

        $statistiquesParClasse[$formation->getNom()]['pourcentageNouveaux'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$formation->getNom()]['nouveaux'] / $totalElevesClasse) * 100
            : 0;
       
        return $this->render('gandaal/administration/secretariat/statistique/statistique_formation_show.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiquesParClasse,
            'inscriptionsGroupByType' => $inscriptionsGroupByType,
            'formation' => $formation,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/classe/show/{etablissement}/{classe}', name: 'app_gandaal_administration_secretaraiat_statistique_classe_show', methods: ['GET'])]
    public function statistiqueClasseShow(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep, 
        FormationRepository $formationRep, 
        CursusRepository $cursusRep, 
        ClasseRepartitionRepository $classeRep, 
        Request $request, 
        ClasseRepartition $classe,
        SessionInterface $session
    ): Response {  
        // Initialisation des variables pour les statistiques par classe
        $statistiquesParClasse = [];

        
        $classeFormation = $classe->getNom();  // Classe associée à la formation

        // Initialiser les statistiques pour cette classe si nécessaire
        if (!isset($statistiquesParClasse[$classeFormation])) {
            $statistiquesParClasse[$classeFormation] = [
                'effectifFilles' => 0,
                'effectifGarcons' => 0,
                'totalEleves' => 0,
                'anciens' => 0,
                'nouveaux' => 0,
                'abandons' => 0,
                'redoublants' => 0,
                'classe' => $classe
            ];
        }

        // Récupérer les inscriptions pour la classe
        $inscriptions = $inscriptionRep->listeGeneraleDesElevesInscritsParClasse($classe);

        $inscriptionsGroupByType = [];

        foreach ($inscriptions as $inscription) {
            $type = $inscription->getType();
            
            // Si le type n'est pas encore dans le tableau, l'initialiser
            if (!isset($inscriptionsGroupByType[$type])) {
                $inscriptionsGroupByType[$type] = [];
            }

            // Ajouter l'inscription au tableau du type correspondant
            $inscriptionsGroupByType[$type][] = $inscription;
        }

            
        foreach ($inscriptions as $inscription) {
            if ($inscription->getStatut() != 'inactif') {
                $eleve = $inscription->getEleve();
                $sexe = $eleve->getSexe();

                // Compter filles et garçons
                if ($sexe == 'f') {
                    $statistiquesParClasse[$classeFormation]['effectifFilles']++;
                } elseif ($sexe == 'm') {
                    $statistiquesParClasse[$classeFormation]['effectifGarcons']++;
                }

                // Compter nouveaux et anciens
                if ($inscription->getType() == 'inscription') {
                    $statistiquesParClasse[$classeFormation]['nouveaux']++;
                } else {
                    $statistiquesParClasse[$classeFormation]['anciens']++;
                }

                // Compter redoublants
                if ($inscription->getEtatScol() == 'redoublant') {
                    $statistiquesParClasse[$classeFormation]['redoublants']++;
                }

                // Incrémenter le nombre total d'élèves pour cette classe
                $statistiquesParClasse[$classeFormation]['totalEleves']++;
            }

            // Compter les abandons (statut inactif)

            if ($inscription->getStatut() == 'inactif') {
                
                $statistiquesParClasse[$classeFormation]['abandons']++;
            }
        }
        // Calcul des pourcentages pour cette classe
        $totalElevesClasse = $statistiquesParClasse[$classeFormation]['totalEleves'];
        $statistiquesParClasse[$classeFormation]['pourcentageFilles'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$classeFormation]['effectifFilles'] / $totalElevesClasse) * 100
            : 0;

        $statistiquesParClasse[$classeFormation]['pourcentageGarcons'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$classeFormation]['effectifGarcons'] / $totalElevesClasse) * 100
            : 0;

        $statistiquesParClasse[$classeFormation]['pourcentageNouveaux'] = $totalElevesClasse > 0
            ? ($statistiquesParClasse[$classeFormation]['nouveaux'] / $totalElevesClasse) * 100
            : 0;

       
        return $this->render('gandaal/administration/secretariat/statistique/statistique_classe_show.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiquesParClasse,
            'inscriptionsGroupByType' => $inscriptionsGroupByType,
            'classe' => $classe,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/remise/scolarite/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_remise_scolarire', methods: ['GET'])]
    public function remise(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep,
        SessionInterface $session,
        ClasseRepartitionRepository $classeRep
    ): Response {  

        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));
            $remisesScolarite = $inscriptionRep->listeDesRemises($session->get('promo'), $etablissement,  NULL, 1, $classes);

        }else{
            $remisesScolarite = $inscriptionRep->listeDesRemises($session->get('promo'), $etablissement,  NULL, 1);
        }

       // Tableau pour regrouper par classe
        $groupedByClass = [];

        // Tableau séparé pour les remises de 100%
        $groupedByClassOffert = [];

        foreach ($remisesScolarite as $inscription) {
            $classeNom = $inscription->getClasse()->getNom();

            // Créer le groupe de la classe s'il n'existe pas
            if (!isset($groupedByClass[$classeNom])) {
                $groupedByClass[$classeNom] = [
                    'inscriptions' => [],
                ];
            }

            // Si l'inscription a une remise de 100%
            if ($inscription->getRemiseScolarite() == 100.0) {
                // Ajouter l'inscription dans un tableau séparé pour les remises de 100%
                $groupedByClassOffert[] = $inscription;
            } else {
                // Ajouter l'inscription dans la liste générale pour cette classe
                $groupedByClass[$classeNom]['inscriptions'][] = $inscription;
            }
        }
        uksort($groupedByClass, 'strnatcmp');

        // dd($groupedByClassOffert);
        // Affichage des résultats dans le template
        return $this->render('gandaal/administration/secretariat/statistique/remise_scolarite.html.twig', [
            'etablissement' => $etablissement,
            'groupedByClass' => $groupedByClass,
            'groupedByClassOffert' => $groupedByClassOffert,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/remise/inscription/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_remise_inscription', methods: ['GET'])]
    public function remiseInscription(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep,
        SessionInterface $session,
        ClasseRepartitionRepository $classeRep,
    ): Response {  

        
        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));
            $remisesScolarite = $inscriptionRep->listeDesRemises($session->get('promo'), $etablissement, 1, NULL, $classes);

        }else{
            $remisesScolarite = $inscriptionRep->listeDesRemises($session->get('promo'), $etablissement, 1, NULL);
        }
       // Tableau pour regrouper par classe
        $groupedByClass = [];

        // Tableau séparé pour les remises de 100%
        $groupedByClassOffert = [];

        foreach ($remisesScolarite as $inscription) {
            $classeNom = $inscription->getClasse()->getNom();

            // Créer le groupe de la classe s'il n'existe pas
            if (!isset($groupedByClass[$classeNom])) {
                $groupedByClass[$classeNom] = [
                    'inscriptions' => [],
                ];
            }

            // Si l'inscription a une remise de 100%
            if ($inscription->getRemiseScolarite() == 100.0) {
                // Ajouter l'inscription dans un tableau séparé pour les remises de 100%
                $groupedByClassOffert[] = $inscription;
            } else {
                // Ajouter l'inscription dans la liste générale pour cette classe
                $groupedByClass[$classeNom]['inscriptions'][] = $inscription;
            }
        }
        uksort($groupedByClass, 'strnatcmp');

        // dd($groupedByClassOffert);
        // Affichage des résultats dans le template
        return $this->render('gandaal/administration/secretariat/statistique/remise_inscription.html.twig', [
            'etablissement' => $etablissement,
            'groupedByClass' => $groupedByClass,
            'groupedByClassOffert' => $groupedByClassOffert,
            'promo' => $session->get('promo'),
        ]);
    }


    #[Route('/abandon/scolarite/{etablissement}', name: 'app_gandaal_administration_secretaraiat_statistique_abandon_scolarite', methods: ['GET'])]
    public function abandonScolarite(
        Etablissement $etablissement, 
        InscriptionRepository $inscriptionRep,
        SessionInterface $session,
        ClasseRepartitionRepository $classeRep,
    ): Response {  

        
        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));

            $abandons = $inscriptionRep->findBy(['statut' => 'inactif', 'promo' => $session->get('promo'), 'classe' => $classes]);

        }else{
            $abandons = $inscriptionRep->findBy(['statut' => 'inactif', 'promo' => $session->get('promo'), 'etablissement' => $etablissement]);
        }
       // Tableau pour regrouper par classe
        $groupedByClass = [];

        foreach ($abandons as $inscription) {
            $classeNom = $inscription->getClasse()->getNom();

            // Créer le groupe de la classe s'il n'existe pas
            if (!isset($groupedByClass[$classeNom])) {
                $groupedByClass[$classeNom] = [
                    'inscriptions' => [],
                ];
            }  
            $groupedByClass[$classeNom]['inscriptions'][] = $inscription;
            
        }
        uksort($groupedByClass, 'strnatcmp');

        
        return $this->render('gandaal/administration/secretariat/statistique/abandon_scolarite.html.twig', [
            'etablissement' => $etablissement,
            'groupedByClass' => $groupedByClass,
            'promo' => $session->get('promo'),
        ]);
    }


    
}
