<?php

namespace App\Controller\Gandaal\Administration\Pedagogie;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Eleve;
use App\Entity\Inscription;
use App\Entity\Etablissement;
use App\Entity\PaiementEleve;
use App\Service\FonctionService;
use App\Entity\ClasseRepartition;
use App\Entity\ConfigModePaiement;
use App\Entity\DevoirEleve;
use App\Entity\Matiere;
use App\Repository\EleveRepository;
use App\Repository\CursusRepository;
use App\Repository\MatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\NoteEleveRepository;
use App\Entity\PaiementActiviteScolaire;
use App\Repository\DevoirEleveRepository;
use App\Repository\InscriptionRepository;
use App\Repository\ConfigCaisseRepository;
use App\Repository\ControlEleveRepository;
use App\Repository\PaiementEleveRepository;
use App\Repository\FraisScolariteRepository;
use App\Repository\TranchePaiementRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\FraisInscriptionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClasseRepartitionRepository;
use App\Repository\ConfigFonctionRepository;
use App\Repository\ConfigModePaiementRepository;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\ConfigurationModuleRepository;
use App\Repository\EventRepository;
use App\Repository\InscriptionActiviteRepository;
use App\Repository\PaiementActiviteScolaireRepository;
use App\Repository\PaiementSalairePersonnelRepository;
use App\Repository\PersonnelActifRepository;
use App\Repository\PersonnelRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gandaal/administration/Pedagogie/pdf')]
class PdfController extends AbstractController
{
    #[Route('/bulletin/{classe}', name: 'app_gandaal_administration_pedagogie_pdf_bulletin')]
    public function index(
        ClasseRepartition $classe, 
        FonctionService $fonctionService, 
        Request $request, 
        SessionInterface $session, 
        MatiereRepository $matiereRep,
        DevoirEleveRepository $devoirRep, 
        NoteEleveRepository $noteRep, 
        InscriptionRepository $inscriptionRep, 
        CursusRepository $cursusRep,  
        FormationRepository $formationRep,
        ControlEleveRepository $controlEleveRep,
        PersonnelRepository $personnelRep,
        ConfigFonctionRepository $fonctionRep,
        ConfigurationModuleRepository $moduleRep,
        ConfigurationLogicielRepository $configLogicielRep,

    ): Response
    {
        $etablissement = $classe->getFormation()->getCursus()->getEtablissement();        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/config/'.$etablissement->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $symbolePath = $this->getParameter('kernel.project_dir') . '/public/images/config/symbole.png';
        $symboleBase64 = base64_encode(file_get_contents($symbolePath));
        $ministerePath = $this->getParameter('kernel.project_dir') . '/public/images/config/mepua.jpg';
        $ministereBase64 = base64_encode(file_get_contents($ministerePath));

        $periode_select = $request->get("periode") ?: null;
        if ($periode_select) {
            $periode = date("Y") . '-' . $periode_select . '-01';
            $date = new \DateTime($periode);
            $mois_francais = $fonctionService->getMoisEnFrancais($date);
        } else {
            $periode = null;
            $mois_francais = null;
        }
        $trimestre = $request->get("trimestre") ?: null;
        if ($request->get("inscription")) {
            $search_inscription = $inscriptionRep->find($request->get("inscription"));
        }else{
            $search_inscription = null;
        }

        
        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($classe, 'inactif');

          // Récupération des matières
        $matieres = $matiereRep->findBy(['formation' => $classe ? $classe->getFormation() : ''], ['categorie' => 'ASC', 'nom' => 'ASC']);
        $matieresIndex = [];
        foreach ($matieres as $matiere) {
            $matieresIndex[$matiere->getId()] = $matiere;
        }

        // Initialiser les variables pour les moyennes par élève
        $moyennesParEleve = [];
        $moyenneGenerale = [];
        $moyennesParMatiere = [];
        $effectifClasse = 0;
        $effectifEvalue = 0;
        $moyenneClasse = 0;
        $ecartType = 0;
        $moyennePlusElevee = 0;
        $moyennePlusFaible = 0;


        if ($trimestre or $periode) {
            // Récupération des devoirs et des notes
            if ($trimestre) {
                if ($trimestre == 'annuel') {
                    $devoirs = $devoirRep->listeDevoirsAnnuel($classe, $session->get('promo'));
                } else {
                    $devoirs = $devoirRep->findBy(['classe' => $classe, 'periode' => $trimestre, 'promo' => $session->get('promo')]);
                }
            } else {
                $devoirs = $devoirRep->listeDevoirsParMois($periode_select, $classe, $session->get('promo'));
            }

            $notes = $noteRep->findBy(['devoir' => $devoirs]);

            $moyennesParMatiereGlobal = [];

            foreach ($inscriptions as $inscription) {
                $notesEleve = array_filter($notes, fn($note) => $note->getInscription()->getId() === $inscription->getId());

                $moyenneEleve = [];
                $aDesNotes = false;

                foreach ($matieres as $matiere) {
                    $categorieMatiere = $matiere->getCategorie()->getNom(); // Récupérer la catégorie de la matière
                    $moyenneEleve[$categorieMatiere][$matiere->getId()] = [
                        'matiere' => $matiere,
                        'coefficient' => $matiere->getCoef(),
                        'somme_notes_cours' => 0,
                        'nombre_notes_cours' => 0,
                        'somme_notes_composition' => 0,
                        'nombre_notes_composition' => 0,
                        'moyenne' => 'NE',
                        'moyenne_ponderee' => 0,
                        'rang_matiere' => 'NE',  // Ajout de l'entrée pour le rang
                        'appreciation' => 'NE',
                    ];

                    // Initialiser la moyenne globale à 'NE'
                    $moyennesParMatiereGlobal[$matiere->getId()][$inscription->getId()] = 'NE';
                }

                foreach ($notesEleve as $noteEleve) {
                    $matiere = $noteEleve->getDevoir()->getMatiere();
                    $categorieMatiere = $matiere->getCategorie()->getNom();
                    $typeDevoir = $noteEleve->getDevoir()->getTypeDevoir();
                    $coefDevoir = $noteEleve->getDevoir()->getCoef();

                    if ($typeDevoir === 'note de cours') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_cours'] += $noteEleve->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_cours'] += $coefDevoir;
                        $aDesNotes = true;
                    } elseif ($typeDevoir === 'composition') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_composition'] += $noteEleve->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_composition'] += $coefDevoir;
                        $aDesNotes = true;
                    }
                }
                // Si l'élève n'a pas de notes dans toutes les matières, on l'exclut du calcul
                if (!$aDesNotes) {
                    $moyennesParEleve[$inscription->getId()] = [
                        'inscription' => $inscription,
                        'moyennes' => 'NE', // Marquer comme non évalué
                        'rang' => 'NE' // Ajouter rang pour éviter les erreurs de clé
                    ];
                    continue; // Passer à l'élève suivant
                }

                foreach ($moyenneEleve as $categorie => &$matieresEleve) {
                    foreach ($matieresEleve as &$detailsMatiere) {
                        $moyenneNoteCours = ($detailsMatiere['nombre_notes_cours'] > 0) ? ($detailsMatiere['somme_notes_cours'] / $detailsMatiere['nombre_notes_cours']) : Null;
                        $moyenneNoteComposition = ($detailsMatiere['nombre_notes_composition'] > 0) ? ($detailsMatiere['somme_notes_composition'] / $detailsMatiere['nombre_notes_composition']) : Null;

                        if ($detailsMatiere['nombre_notes_cours'] > 0 || $detailsMatiere['nombre_notes_composition'] > 0) {
                            if ($classe->getFormation()->getCursus()->getNom() == 'collège' or $classe->getFormation()->getCursus()->getNom() == 'lycée') {
                                if ($periode) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?: $moyenneNoteCours);
                                }

                                if ($trimestre) {
                                    if ($moyenneNoteCours !== null && $moyenneNoteComposition !== null) {
                                        $detailsMatiere['moyenne'] = ($moyenneNoteCours + 2 * $moyenneNoteComposition) / 3;
                                    }elseif ($moyenneNoteCours !== null && $moyenneNoteComposition == null) {
                                        $detailsMatiere['moyenne'] = ($moyenneNoteCours);
                                    }elseif ($moyenneNoteCours == null && $moyenneNoteComposition !== null) {
                                        $detailsMatiere['moyenne'] = ($moyenneNoteComposition);
                                    }else {
                                        $detailsMatiere['moyenne'] = 0;
                                    }
                                }

                                if ($detailsMatiere['moyenne'] == 0) {
                                    $detailsMatiere['appreciation'] = '';
                                } elseif ($detailsMatiere['moyenne'] >= 0 && $detailsMatiere['moyenne'] <= 5) {
                                    $detailsMatiere['appreciation'] = 'Faible';
                                } elseif ($detailsMatiere['moyenne'] > 5 && $detailsMatiere['moyenne'] < 10) {
                                    $detailsMatiere['appreciation'] = 'Insuffisant';
                                } elseif ($detailsMatiere['moyenne'] >= 10 && $detailsMatiere['moyenne'] < 11) {
                                    $detailsMatiere['appreciation'] = 'Passable';
                                } elseif ($detailsMatiere['moyenne'] >= 11 && $detailsMatiere['moyenne'] < 14) {
                                    $detailsMatiere['appreciation'] = 'Assez-bien';
                                }elseif ($detailsMatiere['moyenne'] >= 14 && $detailsMatiere['moyenne'] < 16) {
                                    $detailsMatiere['appreciation'] = 'Bien';
                                } else {
                                    $detailsMatiere['appreciation'] = 'Très-Bien';
                                }

                            } elseif ($classe->getFormation()->getCursus()->getNom() == 'crèche' or $classe->getFormation()->getCursus()->getNom() == 'maternelle' or $classe->getFormation()->getCursus()->getNom() == 'primaire') {
                                if ($periode) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?: $moyenneNoteCours);
                                }

                                if ($trimestre) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?: $moyenneNoteCours);
                                }

                                if ($detailsMatiere['moyenne'] == 0) {
                                    $detailsMatiere['appreciation'] = '';
                                } elseif ($detailsMatiere['moyenne'] > 0 && $detailsMatiere['moyenne'] < 5) {
                                    $detailsMatiere['appreciation'] = 'Insuffisant';
                                } elseif ($detailsMatiere['moyenne'] >= 5 && $detailsMatiere['moyenne'] < 6) {
                                    $detailsMatiere['appreciation'] = 'Passable';
                                } elseif ($detailsMatiere['moyenne'] >= 6 && $detailsMatiere['moyenne'] < 8) {
                                    $detailsMatiere['appreciation'] = 'Assez-Bien';
                                } elseif ($detailsMatiere['moyenne'] >= 8 && $detailsMatiere['moyenne'] < 10) {
                                    $detailsMatiere['appreciation'] = 'Bien';
                                } else {
                                    $detailsMatiere['appreciation'] = 'Très-Bien';
                                }

                            } else { // université
                                if ($periode) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?: $moyenneNoteCours);
                                }

                                if ($trimestre) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteCours + 2 * $moyenneNoteComposition) / 3;
                                }
                            }

                            // Calcul de la moyenne pondérée
                            $detailsMatiere['moyenne_ponderee'] = $detailsMatiere['moyenne'] * $detailsMatiere['coefficient'];

                            // Mettre à jour la moyenne globale seulement si elle est définie
                            $moyennesParMatiereGlobal[$detailsMatiere['matiere']->getId()][$inscription->getId()] = $detailsMatiere['moyenne'];
                        }
                    }
                }

                $moyennesParEleve[$inscription->getId()] = [
                    'inscription' => $inscription,
                    'moyennes' => $moyenneEleve,
                ];
            }

            // rang des matières
            
            foreach ($moyennesParMatiereGlobal as $matiereId => $moyennesEleves) {
                // Filtrer les moyennes pour exclure les valeurs 'NE'
                $moyennesEleves = array_filter($moyennesEleves, fn($moyenne) => $moyenne !== 'NE');

                // Trier les moyennes par ordre décroissant (le plus haut en premier)
                arsort($moyennesEleves);

                // Initialisation des variables pour le classement
                $currentRank = 1;    // Rang actuel
                $lastMoyenne = null; // Dernière moyenne observée
                $elevesAvecMemeRang = 1;  // Compteur pour les élèves ayant le même rang (ex aequo)

                foreach ($moyennesEleves as $eleveId => $moyenne) {
                    // Vérifier si la moyenne actuelle est égale à la dernière moyenne observée (pour les ex aequo)
                    if ($moyenne === $lastMoyenne) {
                        // Si c'est le cas, tous les élèves ayant la même moyenne obtiennent le même rang
                        $elevesAvecMemeRang++;
                    } else {
                        // Sinon, avancer dans le classement seulement si ce n'est pas le premier tour
                        if ($lastMoyenne !== null) {
                            $currentRank += $elevesAvecMemeRang;
                        }
                        // Réinitialiser le compteur des ex aequo
                        $elevesAvecMemeRang = 1;
                    }

                    // Attribuer le rang à l'élève en fonction de la moyenne et de la matière
                    foreach ($moyennesParEleve[$eleveId]['moyennes'] as $categorie => $matieres) {
                        if (isset($matieres[$matiereId]['moyenne']) && $matieres[$matiereId]['moyenne'] !== 'NE') {
                            // Assigner le rang à cet élève pour la matière en question
                            $moyennesParEleve[$eleveId]['moyennes'][$categorie][$matiereId]['rang_matiere'] = $currentRank;
                            break; // Sortir de la boucle dès qu'on a trouvé la bonne catégorie
                        }
                    }

                    // Mettre à jour la dernière moyenne observée
                    $lastMoyenne = $moyenne;
                }
            }


           // Initialisation des variables
            $sommeMoyennesGenerales = 0;
            $effectifEvalue = 0;
            $moyenneGenerale = [];            
            // dd($moyennesParEleve);
            foreach ($moyennesParEleve as $inscriptionId => $data) {
                $somme = 0;
                $coeffTotal = 0;
                $moyennes = $data['moyennes'];
                // dd($moyennes);
                if (is_array($moyennes)) {
                    foreach ($moyennes as $categorie => $matieres) {
                        if (is_array($matieres)) {
                            foreach ($matieres as $detailsMatiereCles) {
                                // Vérification de la présence des clés 'moyenne_ponderee' et 'coefficient'
                                $moyennePonderee = isset($detailsMatiereCles['moyenne_ponderee']) ? $detailsMatiereCles['moyenne_ponderee'] : 0;
                                $coefficient = isset($detailsMatiereCles['coefficient']) ? $detailsMatiereCles['coefficient'] : 0;

                                // Calcul de la somme pondérée et du total des coefficients
                                $somme += $moyennePonderee;
                                $coeffTotal += $coefficient;
                            }
                        }
                    }
                    // dump($moyennesParEleve);
                    // Calcul de la moyenne générale pour l'élève s'il y a des coefficients
                    if ($coeffTotal > 0) {
                        $comportements = $controlEleveRep->listeDesControlesParEleveGroupe($inscriptionId, 'non justifié', $periode_select, $trimestre) ;

                        $moyenneGenerale[$inscriptionId] = $somme / $coeffTotal;
                        $moyenneGenerale[$inscriptionId] = [
                            'somme' => $somme,
                            'coefTotal' => $coeffTotal,
                            'moyenneGenerale' => $somme / $coeffTotal,
                            'comportements' => $comportements,
                        ];

                        $sommeMoyennesGenerales += $moyenneGenerale[$inscriptionId]['moyenneGenerale'];
                        $effectifEvalue++;
                    } else {
                        // Si aucun coefficient, la moyenne générale est définie à 0 ou 'NE'
                        $moyenneGenerale[$inscriptionId]['moyenneGenerale'] = 'NE';
                    }
                }else {
                    $moyenneGenerale[$inscriptionId] = 'NE'; // Si $moyennes n'est pas un tableau
                }
            }
        // dd($moyennesParEleve);
            // Calcul de la moyenne de la classe
            $moyenneClasse = $effectifEvalue > 0 ? $sommeMoyennesGenerales / $effectifEvalue : 0;
        
    
            // Trier les élèves par moyenne générale pour attribuer les rangs
            $rangs = [];
            // dd($moyenneGenerale);
            foreach ($moyenneGenerale as $id => $moyenne) {
                if (is_array($moyenne)) {
                    
                    if ($moyenne['moyenneGenerale'] !== 'NE') {
                        $rangs[$id] = $moyenne;
                    }
                }
            }
            arsort($rangs); // Trie décroissant
        
            // Attribuer les rangs en gérant les ex-aequo
            $currentRank = 1;
            $lastMoyenne = null;
            $rankOffset = 0;
    
            foreach ($rangs as $id => $moyenne) {
                if ($moyenne === $lastMoyenne) {
                    $moyennesParEleve[$id]['rang'] = $currentRank;
                    $rankOffset++;
                } else {
                    $currentRank += $rankOffset;
                    $rankOffset = 1;
                    $moyennesParEleve[$id]['rang'] = $currentRank;
                    $lastMoyenne = $moyenne;
                }
            }
        
            // Assurer que tous les élèves ont une clé 'rang', même ceux qui sont non évalués
            foreach ($moyennesParEleve as $id => $data) {
                if (!isset($data['rang'])) {
                    $moyennesParEleve[$id]['rang'] = 'NE'; // Valeur par défaut pour non évalué
                }
            }
    
            // Trier les élèves par rang
            uasort($moyennesParEleve, fn($a, $b) => ($a['rang'] === 'NE' ? PHP_INT_MAX : $a['rang']) <=> ($b['rang'] === 'NE' ? PHP_INT_MAX : $b['rang']));
        
            // Calcul des statistiques globales
            $effectifClasse = count($inscriptions);
            $ecartType = 0;
            if ($effectifEvalue > 1) {
                $variance = 0;
                foreach ($moyenneGenerale as $id => $moyenne) {
                    if ($moyenne !== 'NE') {
                        $variance += pow($moyenne['moyenneGenerale'] - $moyenneClasse, 2);
                    }
                }
                $variance /= $effectifEvalue - 1;
                $ecartType = sqrt($variance);
            }
            // Filtrer les moyennes pour exclure les valeurs 'NE'
            $valeursNumeriques = array_filter($moyenneGenerale, fn($value) => $value !== 'NE');

            // Moyenne la plus élevée et la plus faible
            $moyennePlusElevee = !empty($valeursNumeriques) ? max($valeursNumeriques)['moyenneGenerale'] : 0;
            $moyennePlusFaible = !empty($valeursNumeriques) ? min($valeursNumeriques)['moyenneGenerale'] : 0;
            
                
        }

        $responsable = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(2)]) ?:Null;
        $responsable_primaire = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(10)]) ?:Null;
        $responsable_college = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(9)]) ?:Null;
        $responsable_lycee = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(12)]) ?:Null;
        
        $dataEleves = [];
        if ($search_inscription) {
            $dataEleves[$search_inscription->getId()] = 
                $moyennesParEleve[$search_inscription->getId()]
            ;
        }else{
            $dataEleves = $moyennesParEleve;
        }        

        $module = $moduleRep->moduleParCursusParPeriodeParEtablissement($classe->getFormation()->getCursus(), $periode_select, $etablissement);
        
        if ($session->get('configLogiciel')->getFormatBulletin() and $session->get('configLogiciel')->getFormatBulletin() == 'format1') {
            return $this->render('gandaal/administration/pedagogie/pdf/bulletin_mois_format1.html.twig', [
                'logoPath' => $logoBase64,
                'symbolePath' => $symboleBase64,
                'ministerePath' => $ministereBase64,
                'etablissement' => $etablissement,
                'mois_francais' => $mois_francais,
                'promo' => $session->get('promo'),
                'inscriptions' => $inscriptions,
                'periode' => $periode ?? null,
                'periode_select' => $periode_select ?? null,
                'trimestre' => $trimestre,
                'search_classe' => $classe,
                'matieres' => $matieres,
                'moyennesParEleve' => $dataEleves,
                'moyenneGenerale' => $moyenneGenerale,
                'moyennesParMatiere' => $moyennesParMatiere,
                'effectifClasse' => $effectifClasse,
                'effectifEvalue' => $effectifEvalue,
                'moyenneClasse' => $moyenneClasse,
                'ecartType' => $ecartType,
                'moyennePlusElevee' => $moyennePlusElevee,
                'moyennePlusFaible' => $moyennePlusFaible,
                'promo' => $session->get('promo'),
                'mois_francais' => $mois_francais,
                'classe' => $classe,
                'responsable' => $responsable,
                'responsable_primaire' => $responsable_primaire,
                'responsable_college' => $responsable_college,
                'responsable_lycee' => $responsable_lycee,
                'serach_inscription' => $search_inscription,
                'module' => $module,
            ]);
        }else{

            return $this->render('gandaal/administration/pedagogie/pdf/bulletin_mois.html.twig', [
                'logoPath' => $logoBase64,
                'symbolePath' => $symboleBase64,
                'ministerePath' => $ministereBase64,
                'etablissement' => $etablissement,
                'mois_francais' => $mois_francais,
                'promo' => $session->get('promo'),
                'inscriptions' => $inscriptions,
                'periode' => $periode ?? null,
                'periode_select' => $periode_select ?? null,
                'trimestre' => $trimestre,
                'search_classe' => $classe,
                'matieres' => $matieres,
                'moyennesParEleve' => $dataEleves,
                'moyenneGenerale' => $moyenneGenerale,
                'moyennesParMatiere' => $moyennesParMatiere,
                'effectifClasse' => $effectifClasse,
                'effectifEvalue' => $effectifEvalue,
                'moyenneClasse' => $moyenneClasse,
                'ecartType' => $ecartType,
                'moyennePlusElevee' => $moyennePlusElevee,
                'moyennePlusFaible' => $moyennePlusFaible,
                'promo' => $session->get('promo'),
                'mois_francais' => $mois_francais,
                'classe' => $classe,
                'responsable' => $responsable,
                'responsable_primaire' => $responsable_primaire,
                'responsable_college' => $responsable_college,
                'responsable_lycee' => $responsable_lycee,
                'serach_inscription' => $search_inscription,
                'module' => $module,
            ]);
        }
    }

    #[Route('/bulletin/annuel/{classe}', name: 'app_gandaal_administration_pedagogie_pdf_bulletin_annuel')]
    public function bulletinAnnuel(
        ClasseRepartition $classe, 
        FonctionService $fonctionService, 
        Request $request, 
        SessionInterface $session, 
        MatiereRepository $matiereRep,
        DevoirEleveRepository $devoirRep, 
        NoteEleveRepository $noteRep, 
        InscriptionRepository $inscriptionRep, 
        CursusRepository $cursusRep,  
        FormationRepository $formationRep,
        ControlEleveRepository $controlEleveRep,
        PersonnelRepository $personnelRep,
        ConfigFonctionRepository $fonctionRep,

    ): Response
    {
        $etablissement = $classe->getFormation()->getCursus()->getEtablissement();        
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/config/'.$etablissement->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $symbolePath = $this->getParameter('kernel.project_dir') . '/public/images/config/symbole.png';
        $symboleBase64 = base64_encode(file_get_contents($symbolePath));
        $ministerePath = $this->getParameter('kernel.project_dir') . '/public/images/config/mepua.jpg';
        $ministereBase64 = base64_encode(file_get_contents($ministerePath));
       

        $periode_select = $request->get("periode") ?: null;
        if ($periode_select) {
            $periode = date("Y") . '-' . $periode_select . '-01';
            $date = new \DateTime($periode);
            $mois_francais = $fonctionService->getMoisEnFrancais($date);
        } else {
            $periode = null;
            $mois_francais = null;
        }
        $trimestre = $request->get("trimestre") ?: null;
        if ($request->get("inscription")) {
            $search_inscription = $inscriptionRep->find($request->get("inscription"));
        }else{
            $search_inscription = null;
        }

        
        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($classe, 'inactif');

          // Récupération des matières
        $matieres = $matiereRep->findBy(['formation' => $classe ? $classe->getFormation() : ''], ['categorie' => 'ASC', 'nom' => 'ASC']);
        $matieresIndex = [];
        foreach ($matieres as $matiere) {
            $matieresIndex[$matiere->getId()] = $matiere;
        }

        // Initialiser les variables pour les moyennes par élève
        $moyennesParEleve = [];
        $moyenneGenerale = [];
        $moyennesParMatiere = [];
        $effectifClasse = 0;
        $effectifEvalue = 0;
        $moyenneClasse = 0;
        $ecartType = 0;
        $moyennePlusElevee = 0;
        $moyennePlusFaible = 0;


        if ($trimestre == 'annuel') {
            // Récupération des devoirs et des notes
            
            
            $devoirs = $devoirRep->listeDevoirsAnnuel($classe, $session->get('promo'));
            
            $devoirs_trimestre1 = $devoirRep->findBy(['classe' => $classe, 'periode' => 1, 'promo' => $session->get('promo')]);            
            $devoirs_trimestre2 = $devoirRep->findBy(['classe' => $classe, 'periode' => 2, 'promo' => $session->get('promo')]);
            $devoirs_trimestre3 = $devoirRep->findBy(['classe' => $classe, 'periode' => 3, 'promo' => $session->get('promo')]);
            
            

            $notes = $noteRep->findBy(['devoir' => $devoirs]); // notes annuelles
            $notes_trimestre1 = $noteRep->findBy(['devoir' => $devoirs_trimestre1]); // notes trimestre 1
            $notes_trimestre2 = $noteRep->findBy(['devoir' => $devoirs_trimestre2]); // notes trimestre 2
            $notes_trimestre3 = $noteRep->findBy(['devoir' => $devoirs_trimestre3]); // notes trimestre 3

            $moyennesParMatiereGlobal = [];

            foreach ($inscriptions as $inscription) {
                $notesEleve = array_filter($notes, fn($note) => $note->getInscription()->getId() === $inscription->getId());
                $notesEleveT1 = array_filter($notes_trimestre1, fn($note) => $note->getInscription()->getId() === $inscription->getId());
                $notesEleveT2 = array_filter($notes_trimestre2, fn($note) => $note->getInscription()->getId() === $inscription->getId());
                $notesEleveT3 = array_filter($notes_trimestre3, fn($note) => $note->getInscription()->getId() === $inscription->getId());

                $moyenneEleve = [];
                $aDesNotes = false;

                foreach ($matieres as $matiere) {
                    $categorieMatiere = $matiere->getCategorie()->getNom(); 
                    $moyenneEleve[$categorieMatiere][$matiere->getId()] = [
                        'matiere' => $matiere,
                        'coefficient' => $matiere->getCoef(),
                        'somme_notes_cours_T1' => 0,
                        'nombre_notes_cours_T1' => 0,
                        'somme_notes_composition_T1' => 0,
                        'nombre_notes_composition_T1' => 0,

                        'somme_notes_cours_T2' => 0,
                        'nombre_notes_cours_T2' => 0,
                        'somme_notes_composition_T2' => 0,
                        'nombre_notes_composition_T2' => 0,

                        'somme_notes_cours_T3' => 0,
                        'nombre_notes_cours_T3' => 0,
                        'somme_notes_composition_T3' => 0,
                        'nombre_notes_composition_T3' => 0,

                        'somme_notes_cours' => 0,
                        'nombre_notes_cours' => 0,
                        'somme_notes_composition' => 0,
                        'nombre_notes_composition' => 0,

                        'moyenne' => 'NE',
                        'moyenne_1' => 'NE',
                        'moyenne_2' => 'NE',
                        'moyenne_3' => 'NE',
                        'moyenne_ponderee' => 0,
                        'moyenne_pondereeT1' => 0,
                        'moyenne_pondereeT2' => 0,
                        'moyenne_pondereeT3' => 0,
                        'rang_matiere' => 'NE',  // Ajout de l'entrée pour le rang
                        'appreciation' => 'NE',
                    ];
                }

                // Calcul des moyennes  ANNUEL
                foreach ($notesEleve as $noteEleve) {
                    $matiere = $noteEleve->getDevoir()->getMatiere();
                    $categorieMatiere = $matiere->getCategorie()->getNom();
                    $typeDevoir = $noteEleve->getDevoir()->getTypeDevoir();
                    $coefDevoir = $noteEleve->getDevoir()->getCoef();

                    if ($typeDevoir === 'note de cours') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_cours'] += $noteEleve->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_cours'] += $coefDevoir;
                        $aDesNotes = true;
                    } elseif ($typeDevoir === 'composition') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_composition'] += $noteEleve->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_composition'] += $coefDevoir;
                        $aDesNotes = true;
                    }
                }

                // Calcul des moyennes  trimestre 1
                foreach ($notesEleveT1 as $noteEleveT1) {
                    $matiere = $noteEleveT1->getDevoir()->getMatiere();
                    $categorieMatiere = $matiere->getCategorie()->getNom();
                    $typeDevoir = $noteEleveT1->getDevoir()->getTypeDevoir();
                    $coefDevoir = $noteEleveT1->getDevoir()->getCoef();

                    if ($typeDevoir === 'note de cours') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_cours_T1'] += $noteEleveT1->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_cours_T1'] += $coefDevoir;
                        $aDesNotes = true;
                    } elseif ($typeDevoir === 'composition') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_composition_T1'] += $noteEleveT1->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_composition_T1'] += $coefDevoir;
                        $aDesNotes = true;
                    }
                }

                // Calcul des moyennes  trimestre2
                foreach ($notesEleveT2 as $noteEleveT2) {
                    $matiere = $noteEleveT2->getDevoir()->getMatiere();
                    $categorieMatiere = $matiere->getCategorie()->getNom();
                    $typeDevoir = $noteEleveT2->getDevoir()->getTypeDevoir();
                    $coefDevoir = $noteEleveT2->getDevoir()->getCoef();

                    if ($typeDevoir === 'note de cours') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_cours_T2'] += $noteEleveT2->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_cours_T2'] += $coefDevoir;
                        $aDesNotes = true;
                    } elseif ($typeDevoir === 'composition') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_composition_T2'] += $noteEleveT2->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_composition_T2'] += $coefDevoir;
                        $aDesNotes = true;
                    }
                }

                // Calcul des moyennes trimestre3
                foreach ($notesEleveT3 as $noteEleveT3) {
                    $matiere = $noteEleveT3->getDevoir()->getMatiere();
                    $categorieMatiere = $matiere->getCategorie()->getNom();
                    $typeDevoir = $noteEleveT3->getDevoir()->getTypeDevoir();
                    $coefDevoir = $noteEleveT3->getDevoir()->getCoef();

                    if ($typeDevoir === 'note de cours') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_cours_T3'] += $noteEleveT3->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_cours_T3'] += $coefDevoir;
                        $aDesNotes = true;
                    } elseif ($typeDevoir === 'composition') {
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['somme_notes_composition_T3'] += $noteEleveT3->getValeur() * $coefDevoir;
                        $moyenneEleve[$categorieMatiere][$matiere->getId()]['nombre_notes_composition_T3'] += $coefDevoir;
                        $aDesNotes = true;
                    }
                }
                // Si l'élève n'a pas de notes dans toutes les matières, on l'exclut du calcul
                if (!$aDesNotes) {
                    $moyennesParEleve[$inscription->getId()] = [
                        'inscription' => $inscription,
                        'moyennes' => 'NE', // Marquer comme non évalué
                        'moyennes_1' => 'NE', // Marquer comme non évalué
                        'moyennes_2' => 'NE', // Marquer comme non évalué
                        'moyennes_3' => 'NE', // Marquer comme non évalué
                        'rang' => 'NE' // Ajouter rang pour éviter les erreurs de clé
                    ];
                    continue; // Passer à l'élève suivant
                }

                foreach ($moyenneEleve as $categorie => &$matieresEleve) {
                    foreach ($matieresEleve as &$detailsMatiere) {
                        // Moyenne annuelle
                        $moyenneNoteCours = ($detailsMatiere['nombre_notes_cours'] > 0) ? ($detailsMatiere['somme_notes_cours'] / $detailsMatiere['nombre_notes_cours']) : Null;
                        $moyenneNoteComposition = ($detailsMatiere['nombre_notes_composition'] > 0) ? ($detailsMatiere['somme_notes_composition'] / $detailsMatiere['nombre_notes_composition']) : Null;

                        // moyenne trimestre 1
                        $moyenneNoteCoursT1 = ($detailsMatiere['nombre_notes_cours_T1'] > 0) ? ($detailsMatiere['somme_notes_cours_T1'] / $detailsMatiere['nombre_notes_cours_T1']) : Null;
                        $moyenneNoteCompositionT1 = ($detailsMatiere['nombre_notes_composition_T1'] > 0) ? ($detailsMatiere['somme_notes_composition_T1'] / $detailsMatiere['nombre_notes_composition_T1']) : Null;
                        

                        // moyenne trimestre 2
                        $moyenneNoteCoursT2 = ($detailsMatiere['nombre_notes_cours_T2'] > 0) ? ($detailsMatiere['somme_notes_cours_T2'] / $detailsMatiere['nombre_notes_cours_T2']) : Null;
                        $moyenneNoteCompositionT2 = ($detailsMatiere['nombre_notes_composition_T2'] > 0) ? ($detailsMatiere['somme_notes_composition_T2'] / $detailsMatiere['nombre_notes_composition_T2']) : Null;

                        // moyenne trimestre 3
                        $moyenneNoteCoursT3 = ($detailsMatiere['nombre_notes_cours_T3'] > 0) ? ($detailsMatiere['somme_notes_cours_T3'] / $detailsMatiere['nombre_notes_cours_T3']) : Null;
                        $moyenneNoteCompositionT3 = ($detailsMatiere['nombre_notes_composition_T3'] > 0) ? ($detailsMatiere['somme_notes_composition_T3'] / $detailsMatiere['nombre_notes_composition_T3']) : Null;

                        if ($detailsMatiere['nombre_notes_cours'] > 0 || $detailsMatiere['nombre_notes_composition'] > 0) {
                            if ($classe->getFormation()->getCursus()->getNom() == 'collège' or $classe->getFormation()->getCursus()->getNom() == 'lycée') {

                                if ($trimestre) {

                                    if ($moyenneNoteCoursT1 !== null && $moyenneNoteCompositionT1 !== null) {
                                        $detailsMatiere['moyenne_1'] = ($moyenneNoteCoursT1 + 2 * $moyenneNoteCompositionT1) / 3;
                                    }elseif ($moyenneNoteCoursT1 !== null && $moyenneNoteCompositionT1 == null) {
                                        $detailsMatiere['moyenne_1'] = ($moyenneNoteCoursT1);
                                        
                                    }elseif ($moyenneNoteCoursT1 == null && $moyenneNoteCompositionT1 !== null) {
                                        $detailsMatiere['moyenne_1'] = ($moyenneNoteCompositionT1);
                                    }else {
                                        $detailsMatiere['moyenne_1'] = 0;
                                    }

                                    if ($moyenneNoteCoursT2 !== null && $moyenneNoteCompositionT2 !== null) {
                                        $detailsMatiere['moyenne_2'] = ($moyenneNoteCoursT2 + 2 * $moyenneNoteCompositionT2) / 3;
                                    }elseif ($moyenneNoteCoursT2 !== null && $moyenneNoteCompositionT2 == null) {
                                        $detailsMatiere['moyenne_2'] = ($moyenneNoteCoursT2);
                                    }elseif ($moyenneNoteCoursT2 == null && $moyenneNoteCompositionT2 !== null) {
                                        $detailsMatiere['moyenne_2'] = ($moyenneNoteCompositionT2);
                                    }else {
                                        $detailsMatiere['moyenne_2'] = 0;
                                    }

                                    if ($moyenneNoteCoursT3 !== null && $moyenneNoteCompositionT3 !== null) {
                                        $detailsMatiere['moyenne_3'] = ($moyenneNoteCoursT3 + 3 * $moyenneNoteCompositionT3) / 3;
                                    }elseif ($moyenneNoteCoursT3 !== null && $moyenneNoteCompositionT3 == null) {
                                        $detailsMatiere['moyenne_3'] = ($moyenneNoteCoursT3);
                                    }elseif ($moyenneNoteCoursT3 == null && $moyenneNoteCompositionT3 !== null) {
                                        $detailsMatiere['moyenne_3'] = ($moyenneNoteCompositionT3);
                                    }else {
                                        $detailsMatiere['moyenne_3'] = 0;
                                    }

                                    if ($moyenneNoteCours !== null && $moyenneNoteComposition !== null) {
                                        $detailsMatiere['moyenne'] = ($moyenneNoteCours + 2 * $moyenneNoteComposition) / 3;
                                    }elseif ($moyenneNoteCours !== null && $moyenneNoteComposition == null) {
                                        $detailsMatiere['moyenne'] = ($moyenneNoteCours);
                                    }elseif ($moyenneNoteCours == null && $moyenneNoteComposition !== null) {
                                        $detailsMatiere['moyenne'] = ($moyenneNoteComposition);
                                    }else {
                                        $detailsMatiere['moyenne'] = 0;
                                    }
                                }

                                if ($detailsMatiere['moyenne'] == 0) {
                                    $detailsMatiere['appreciation'] = '';
                                } elseif ($detailsMatiere['moyenne'] >= 0 && $detailsMatiere['moyenne'] <= 5) {
                                    $detailsMatiere['appreciation'] = 'Faible';
                                } elseif ($detailsMatiere['moyenne'] > 5 && $detailsMatiere['moyenne'] < 10) {
                                    $detailsMatiere['appreciation'] = 'Insuffisant';
                                } elseif ($detailsMatiere['moyenne'] >= 10 && $detailsMatiere['moyenne'] < 11) {
                                    $detailsMatiere['appreciation'] = 'Passable';
                                } elseif ($detailsMatiere['moyenne'] >= 11 && $detailsMatiere['moyenne'] < 14) {
                                    $detailsMatiere['appreciation'] = 'Assez-bien';
                                }elseif ($detailsMatiere['moyenne'] >= 14 && $detailsMatiere['moyenne'] < 16) {
                                    $detailsMatiere['appreciation'] = 'Bien';
                                } else {
                                    $detailsMatiere['appreciation'] = 'Très-Bien';
                                }

                            } elseif ($classe->getFormation()->getCursus()->getNom() == 'crèche' or $classe->getFormation()->getCursus()->getNom() == 'maternelle' or $classe->getFormation()->getCursus()->getNom() == 'primaire') {

                                if ($trimestre) {
                                    $detailsMatiere['moyenne_1'] = ($moyenneNoteCompositionT1 ?: $moyenneNoteCoursT1);

                                    $detailsMatiere['moyenne_2'] = ($moyenneNoteCompositionT2 ?: $moyenneNoteCoursT2);

                                    $detailsMatiere['moyenne_3'] = ($moyenneNoteCompositionT3 ?: $moyenneNoteCoursT3);


                                    $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?: $moyenneNoteCours);
                                }

                                

                                if ($detailsMatiere['moyenne'] == 0) {
                                    $detailsMatiere['appreciation'] = '';
                                } elseif ($detailsMatiere['moyenne'] > 0 && $detailsMatiere['moyenne'] < 5) {
                                    $detailsMatiere['appreciation'] = 'Insuffisant';
                                } elseif ($detailsMatiere['moyenne'] >= 5 && $detailsMatiere['moyenne'] < 6) {
                                    $detailsMatiere['appreciation'] = 'Passable';
                                } elseif ($detailsMatiere['moyenne'] >= 6 && $detailsMatiere['moyenne'] < 8) {
                                    $detailsMatiere['appreciation'] = 'Assez-Bien';
                                } elseif ($detailsMatiere['moyenne'] >= 8 && $detailsMatiere['moyenne'] < 10) {
                                    $detailsMatiere['appreciation'] = 'Bien';
                                } else {
                                    $detailsMatiere['appreciation'] = 'Très-Bien';
                                }

                            } else { // université
                                if ($periode) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?: $moyenneNoteCours);
                                }

                                if ($trimestre) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteCours + 2 * $moyenneNoteComposition) / 3;
                                }
                            }

                            // Calcul de la moyenne pondérée
                            $detailsMatiere['moyenne_ponderee'] = $detailsMatiere['moyenne'] * $detailsMatiere['coefficient'];

                            $detailsMatiere['moyenne_pondereeT1'] = $detailsMatiere['moyenne_1'] * $detailsMatiere['coefficient'];

                            $detailsMatiere['moyenne_pondereeT2'] = $detailsMatiere['moyenne_2'] * $detailsMatiere['coefficient'];

                            $detailsMatiere['moyenne_pondereeT3'] = $detailsMatiere['moyenne_3'] * $detailsMatiere['coefficient'];

                            // Mettre à jour la moyenne globale seulement si elle est définie
                            $moyennesParMatiereGlobal[$detailsMatiere['matiere']->getId()][$inscription->getId()] = $detailsMatiere['moyenne'];
                        }
                    }
                }

                $moyennesParEleve[$inscription->getId()] = [
                    'inscription' => $inscription,
                    'moyennes' => $moyenneEleve,
                    
                ];
            }

            // rang des matières
            
            foreach ($moyennesParMatiereGlobal as $matiereId => $moyennesEleves) {
                // Filtrer les moyennes pour exclure les valeurs 'NE'
                $moyennesEleves = array_filter($moyennesEleves, fn($moyenne) => $moyenne !== 'NE');

                // Trier les moyennes par ordre décroissant (le plus haut en premier)
                arsort($moyennesEleves);

                // Initialisation des variables pour le classement
                $currentRank = 1;    // Rang actuel
                $lastMoyenne = null; // Dernière moyenne observée
                $elevesAvecMemeRang = 1;  // Compteur pour les élèves ayant le même rang (ex aequo)

                foreach ($moyennesEleves as $eleveId => $moyenne) {
                    // Vérifier si la moyenne actuelle est égale à la dernière moyenne observée (pour les ex aequo)
                    if ($moyenne === $lastMoyenne) {
                        // Si c'est le cas, tous les élèves ayant la même moyenne obtiennent le même rang
                        $elevesAvecMemeRang++;
                    } else {
                        // Sinon, avancer dans le classement seulement si ce n'est pas le premier tour
                        if ($lastMoyenne !== null) {
                            $currentRank += $elevesAvecMemeRang;
                        }
                        // Réinitialiser le compteur des ex aequo
                        $elevesAvecMemeRang = 1;
                    }

                    // Attribuer le rang à l'élève en fonction de la moyenne et de la matière
                    foreach ($moyennesParEleve[$eleveId]['moyennes'] as $categorie => $matieres) {
                        if (isset($matieres[$matiereId]['moyenne']) && $matieres[$matiereId]['moyenne'] !== 'NE') {
                            // Assigner le rang à cet élève pour la matière en question
                            $moyennesParEleve[$eleveId]['moyennes'][$categorie][$matiereId]['rang_matiere'] = $currentRank;
                            break; // Sortir de la boucle dès qu'on a trouvé la bonne catégorie
                        }
                    }

                    // Mettre à jour la dernière moyenne observée
                    $lastMoyenne = $moyenne;
                }
            }


           // Initialisation des variables
            $sommeMoyennesGenerales = 0;
            $sommeMoyennesGeneralesT1 = 0;
            $sommeMoyennesGeneralesT2 = 0;
            $sommeMoyennesGeneralesT3 = 0;
            $effectifEvalue = 0;
            $moyenneGenerale = [];

            foreach ($moyennesParEleve as $inscriptionId => $data) {
                $somme = 0;
                $sommeT1 = 0;
                $sommeT2 = 0;
                $sommeT3 = 0;

                $coeffTotal = 0;
                $moyennes = $data['moyennes'];
                // dd($moyennes);
                if (is_array($moyennes)) {
                    # code...
                    foreach ($moyennes as $categorie => $matieres) {
                        foreach ($matieres as $detailsMatiere) {
                            // Vérification de la présence des clés 'moyenne_ponderee' et 'coefficient'
                            $moyennePonderee = isset($detailsMatiere['moyenne_ponderee']) ? $detailsMatiere['moyenne_ponderee'] : 0;
    
                            $moyennePondereeT1 = isset($detailsMatiere['moyenne_pondereeT1']) ? $detailsMatiere['moyenne_pondereeT1'] : 0;
    
                            $moyennePondereeT2 = isset($detailsMatiere['moyenne_pondereeT2']) ? $detailsMatiere['moyenne_pondereeT2'] : 0;
    
                            $moyennePondereeT3 = isset($detailsMatiere['moyenne_pondereeT3']) ? $detailsMatiere['moyenne_pondereeT3'] : 0;
    
    
                            $coefficient = isset($detailsMatiere['coefficient']) ? $detailsMatiere['coefficient'] : 0;
                            // Calcul de la somme pondérée et du total des coefficients
                            $somme += $moyennePonderee;
                            $sommeT1 += $moyennePondereeT1;
                            $sommeT2 += $moyennePondereeT2;
                            $sommeT3 += $moyennePondereeT3;
                            if ($detailsMatiere['moyenne'] !== 'NE') {
                                $coeffTotal += $coefficient;
                            }
                        }
                    }
                }

                // Calcul de la moyenne générale pour l'élève s'il y a des coefficients
                if ($coeffTotal > 0) {
                    $comportements = $controlEleveRep->listeDesControlesParEleveGroupe($inscriptionId, 'non justifié', $periode_select, $trimestre) ;

                    $moyenneGenerale[$inscriptionId] = [
                        'somme' => $somme,
                        'rang' => 'NE',
                        'sommeT1' => $sommeT1,
                        'sommeT2' => $sommeT2,
                        'sommeT3' => $sommeT3,
                        'coefTotal' => $coeffTotal,
                        'moyenneGenerale' => $somme / $coeffTotal,
                        'moyenneGeneraleT1' => $sommeT1 / $coeffTotal,
                        'moyenneGeneraleT2' => $sommeT2 / $coeffTotal,
                        'moyenneGeneraleT3' => $sommeT3 / $coeffTotal,
                        'comportements' => $comportements,
                        'inscription' => $data['inscription'],

                    ];

                    $sommeMoyennesGenerales += $moyenneGenerale[$inscriptionId]['moyenneGenerale'];
                    $sommeMoyennesGeneralesT1 += $moyenneGenerale[$inscriptionId]['moyenneGeneraleT1'];
                    $sommeMoyennesGeneralesT2 += $moyenneGenerale[$inscriptionId]['moyenneGeneraleT2'];
                    $sommeMoyennesGeneralesT3 += $moyenneGenerale[$inscriptionId]['moyenneGeneraleT3'];

                    $effectifEvalue++;
                } else {
                    // Si aucun coefficient, la moyenne générale est définie à 0 ou 'NE'
                    $moyenneGenerale[$inscriptionId]['moyenneGenerale'] = 'NE';
                }
            }
    
            // Calcul de la moyenne de la classe
            $moyenneClasse = $effectifEvalue > 0 ? $sommeMoyennesGenerales / $effectifEvalue : 0;
            $moyenneClasseT1 = $effectifEvalue > 0 ? $sommeMoyennesGeneralesT1 / $effectifEvalue : 0;
            $moyenneClasseT2 = $effectifEvalue > 0 ? $sommeMoyennesGeneralesT2 / $effectifEvalue : 0;
            $moyenneClasseT3 = $effectifEvalue > 0 ? $sommeMoyennesGeneralesT3 / $effectifEvalue : 0;
        
    
            // Trier les élèves par moyenne générale pour attribuer les rangs
            $rangs = [];
            foreach ($moyenneGenerale as $id => $moyenne) {
               
                if ($moyenne['moyenneGenerale'] !== 'NE') {
                    $rangs[$id] = $moyenne;
                }
            }
            arsort($rangs); // Trie décroissant
        
            // Attribuer les rangs en gérant les ex-aequo
            $currentRank = 1;
            $lastMoyenne = null;
            $rankOffset = 0;
    
            foreach ($rangs as $id => $moyenne) {
                if ($moyenne === $lastMoyenne) {
                    $moyennesParEleve[$id]['rang'] = $currentRank;
                    $moyenneGenerale[$id]['rang'] = $currentRank; // attribution du rang dans la moyenne générale pour le l'option général
                    $rankOffset++;
                } else {
                    $currentRank += $rankOffset;
                    $rankOffset = 1;
                    $moyennesParEleve[$id]['rang'] = $currentRank;
                    $moyenneGenerale[$id]['rang'] = $currentRank; // attribution du rang dans la moyenne générale pour le l'option général
                    $lastMoyenne = $moyenne;
                }
            }
        
            // Assurer que tous les élèves ont une clé 'rang', même ceux qui sont non évalués
            foreach ($moyennesParEleve as $id => $data) {
                if (!isset($data['rang'])) {
                    $moyennesParEleve[$id]['rang'] = 'NE'; // Valeur par défaut pour non évalué
                }
            }

            foreach ($moyenneGenerale as $id => $data) {
                if (!isset($data['rang'])) {
                    $moyenneGenerale[$id]['rang'] = 'NE'; // Valeur par défaut pour non évalué
                }
            }
    
            // Trier les élèves par rang
            uasort($moyennesParEleve, fn($a, $b) => ($a['rang'] === 'NE' ? PHP_INT_MAX : $a['rang']) <=> ($b['rang'] === 'NE' ? PHP_INT_MAX : $b['rang']));

            uasort($moyenneGenerale, function ($a, $b) {
                return $b['moyenneGenerale'] <=> $a['moyenneGenerale'];
            });

            // dd($moyenneGenerale);
        
            // Calcul des statistiques globales
            $effectifClasse = count($inscriptions);
            $ecartType = 0;
            if ($effectifEvalue > 1) {
                $variance = 0;
                foreach ($moyenneGenerale as $id => $moyenne) {
                    if ($moyenne !== 'NE' and $moyenne['moyenneGenerale'] !=='NE') {
                        $variance += pow($moyenne['moyenneGenerale'] - $moyenneClasse, 2);
                    }
                }
                $variance /= $effectifEvalue - 1;
                $ecartType = sqrt($variance);
            }
            // Filtrer les moyennes pour exclure les valeurs 'NE'
            $valeursNumeriques = array_filter($moyenneGenerale, fn($value) => $value !== 'NE');

            // Moyenne la plus élevée et la plus faible
            $moyennePlusElevee = !empty($valeursNumeriques) ? max($valeursNumeriques)['moyenneGenerale'] : 0;
            $moyennePlusFaible = !empty($valeursNumeriques) ? min($valeursNumeriques)['moyenneGenerale'] : 0;
            
                
        }

        $responsable = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(2)]) ?:Null;
        $responsable_primaire = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(10)]) ?:Null;
        $responsable_college = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(9)]) ?:Null;
        $responsable_lycee = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(12)]) ?:Null;

        // gestion recherche élève
        $dataEleves = [];
        if ($search_inscription) {
            $dataEleves[$search_inscription->getId()] = 
                $moyennesParEleve[$search_inscription->getId()]
            ;
        }else{
            $dataEleves = $moyennesParEleve;
        }
        // dd($dataEleves);
        if ($request->get('origine') and $request->get('origine') == 'général') {
            return $this->render('gandaal/administration/pedagogie/pdf/bulletin_general.html.twig', [
                'logoPath' => $logoBase64,
                'symbolePath' => $symboleBase64,
                'ministerePath' => $ministereBase64,
                'etablissement' => $etablissement,
                'mois_francais' => $mois_francais,
                'promo' => $session->get('promo'),
                'inscriptions' => $inscriptions,
                'periode' => $periode ?? null,
                'periode_select' => $periode_select ?? null,
                'trimestre' => $trimestre,
                'search_classe' => $classe,
                'matieres' => $matieres,
                'moyennesParEleve' => $dataEleves,
                'moyenneGenerale' => $moyenneGenerale,
                'moyennesParMatiere' => $moyennesParMatiere,
                'effectifClasse' => $effectifClasse,
                'effectifEvalue' => $effectifEvalue,
                'moyenneClasse' => $moyenneClasse,
                'ecartType' => $ecartType,
                'moyennePlusElevee' => $moyennePlusElevee,
                'moyennePlusFaible' => $moyennePlusFaible,
                'promo' => $session->get('promo'),
                'mois_francais' => $mois_francais,
                'classe' => $classe,
                'responsable' => $responsable,
                'responsable_primaire' => $responsable_primaire,
                'responsable_college' => $responsable_college,
                'responsable_lycee' => $responsable_lycee,
                'serach_inscription' => $search_inscription,
            ]);
        }

        if ($session->get('configLogiciel')->getFormatBulletin() and $session->get('configLogiciel')->getFormatBulletin() == 'format1') {
            return $this->render('gandaal/administration/pedagogie/pdf/bulletin_annuel_format1.html.twig', [
                'logoPath' => $logoBase64,
                'symbolePath' => $symboleBase64,
                'ministerePath' => $ministereBase64,
                'etablissement' => $etablissement,
                'mois_francais' => $mois_francais,
                'promo' => $session->get('promo'),
                'inscriptions' => $inscriptions,
                'periode' => $periode ?? null,
                'periode_select' => $periode_select ?? null,
                'trimestre' => $trimestre,
                'search_classe' => $classe,
                'matieres' => $matieres,
                'moyennesParEleve' => $dataEleves,
                'moyenneGenerale' => $moyenneGenerale,
                'moyennesParMatiere' => $moyennesParMatiere,
                'effectifClasse' => $effectifClasse,
                'effectifEvalue' => $effectifEvalue,
                'moyenneClasse' => $moyenneClasse,
                'ecartType' => $ecartType,
                'moyennePlusElevee' => $moyennePlusElevee,
                'moyennePlusFaible' => $moyennePlusFaible,
                'promo' => $session->get('promo'),
                'mois_francais' => $mois_francais,
                'classe' => $classe,
                'responsable' => $responsable,
                'responsable_primaire' => $responsable_primaire,
                'responsable_college' => $responsable_college,
                'responsable_lycee' => $responsable_lycee,
                'serach_inscription' => $search_inscription,
            ]);
        }else{
            return $this->render('gandaal/administration/pedagogie/pdf/bulletin_annuel.html.twig', [
                'logoPath' => $logoBase64,
                'symbolePath' => $symboleBase64,
                'ministerePath' => $ministereBase64,
                'etablissement' => $etablissement,
                'mois_francais' => $mois_francais,
                'promo' => $session->get('promo'),
                'inscriptions' => $inscriptions,
                'periode' => $periode ?? null,
                'periode_select' => $periode_select ?? null,
                'trimestre' => $trimestre,
                'search_classe' => $classe,
                'matieres' => $matieres,
                'moyennesParEleve' => $dataEleves,
                'moyenneGenerale' => $moyenneGenerale,
                'moyennesParMatiere' => $moyennesParMatiere,
                'effectifClasse' => $effectifClasse,
                'effectifEvalue' => $effectifEvalue,
                'moyenneClasse' => $moyenneClasse,
                'ecartType' => $ecartType,
                'moyennePlusElevee' => $moyennePlusElevee,
                'moyennePlusFaible' => $moyennePlusFaible,
                'promo' => $session->get('promo'),
                'mois_francais' => $mois_francais,
                'classe' => $classe,
                'responsable' => $responsable,
                'responsable_primaire' => $responsable_primaire,
                'responsable_college' => $responsable_college,
                'responsable_lycee' => $responsable_lycee,
                'serach_inscription' => $search_inscription,
            ]);
        }
    }


    #[Route('/classement/{etablissement}/{classe}', name: 'app_gandaal_administration_pedagogie_pdf_classement', methods: ['GET'])]
    public function classement(
        MatiereRepository $matiereRep, 
        ClasseRepartitionRepository $classeRep, 
        DevoirEleveRepository $devoirRep, 
        NoteEleveRepository $noteRep, 
        InscriptionRepository $inscriptionRep, 
        Request $request, 
        PersonnelRepository $personnelRep,
        ConfigFonctionRepository $fonctionRep,
        SessionInterface $session, 
        FonctionService $fonctionService,
        ControlEleveRepository $controlEleveRep,
        Etablissement $etablissement
    ): Response {
       
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/config/'.$etablissement->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $symbolePath = $this->getParameter('kernel.project_dir') . '/public/images/config/symbole.png';
        $symboleBase64 = base64_encode(file_get_contents($symbolePath));
        $ministerePath = $this->getParameter('kernel.project_dir') . '/public/images/config/mepua.jpg';
        $ministereBase64 = base64_encode(file_get_contents($ministerePath));

        if ($request->get('classe')) {
            $classe = $classeRep->find($request->get('classe'));
            $session->set('session_classe_note', $classe);
        }
        $classe = $session->get('session_classe_note');
        
        $periode_select = $request->get("periode") ?: null;
        $trimestre = $request->get("trimestre") ?: null;

        if ($periode_select) {
            $periode = date("Y") . '-' . $periode_select . '-01';
        } else {
            $periode = null;
        }

        if ($periode) {
            $date = new \DateTime($periode);
            $mois_francais = $fonctionService->getMoisEnFrancais($date);
        }else{
            $mois_francais = Null;

        }

        // Récupération des élèves par classe
        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($classe, 'inactif');

        // Récupération des matières
        $matieres = $matiereRep->findBy(['formation' => $classe ? $classe->getFormation() : ''], ['categorie' => 'ASC', 'nom' => 'ASC']);
        $matieresIndex = [];
        foreach ($matieres as $matiere) {
            $matieresIndex[$matiere->getId()] = $matiere;
        }

        // Initialiser les variables pour les moyennes par élève
        $moyennesParEleve = [];
        $moyenneGenerale = [];
        $moyennesParMatiere = [];
        $effectifClasse = 0;
        $effectifEvalue = 0;
        $moyenneClasse = 0;
        $ecartType = 0;
        $moyennePlusElevee = 0;
        $moyennePlusFaible = 0;
        // dd($periode);


        if ($trimestre or $periode) {
            // Récupération des devoirs et des notes
            if ($trimestre) {
                if ($trimestre == 'annuel') {
                    $devoirs = $devoirRep->listeDevoirsAnnuel($classe, $session->get('promo'));
                }else{

                    $devoirs = $devoirRep->findBy(['classe' => $classe, 'periode' => $trimestre, 'promo' => $session->get('promo')]);
                }
            }else{
                $devoirs = $devoirRep->listeDevoirsParMois($periode_select, $classe, $session->get('promo'));
            
            }
            $notes = $noteRep->findBy(['devoir' => $devoirs]);
        
            foreach ($inscriptions as $inscription) {
                $notesEleve = array_filter($notes, fn($note) => $note->getInscription()->getId() === $inscription->getId());
        
                $moyenneEleve = [];
                $aDesNotes = false; // Variable pour vérifier si l'élève a des notes
        
                foreach ($matieres as $matiere) {
                    $moyenneEleve[$matiere->getId()] = [
                        'matiere' => $matiere->getNom(),
                        'coefficient' => $matiere->getCoef(),
                        'somme_notes_cours' => 0,
                        'nombre_notes_cours' => 0,
                        'somme_notes_composition' => 0,
                        'nombre_notes_composition' => 0,
                        'moyenne' => 'NE', // Initialisé à 'NE' par défaut
                        'moyenne_ponderee' => 0
                    ];
                }
        
                foreach ($notesEleve as $noteEleve) {
                    $matiere = $noteEleve->getDevoir()->getMatiere();
                    $typeDevoir = $noteEleve->getDevoir()->getTypeDevoir();
                    $coefDevoir = $noteEleve->getDevoir()->getCoef(); // Récupérer le coefficient du devoir
                
                    if ($typeDevoir === 'note de cours') {
                        $moyenneEleve[$matiere->getId()]['somme_notes_cours'] += $noteEleve->getValeur() * $coefDevoir; // Appliquer le coefficient du devoir
                        $moyenneEleve[$matiere->getId()]['nombre_notes_cours'] += $coefDevoir; // Compter le coefficient dans la moyenne
                        $aDesNotes = true; // L'élève a au moins une note
                    } elseif ($typeDevoir === 'composition') {
                        $moyenneEleve[$matiere->getId()]['somme_notes_composition'] += $noteEleve->getValeur() * $coefDevoir; // Appliquer le coefficient du devoir
                        $moyenneEleve[$matiere->getId()]['nombre_notes_composition'] += $coefDevoir; // Compter le coefficient dans la moyenne
                        $aDesNotes = true; // L'élève a au moins une note
                    }
                }
        
                // Si l'élève n'a pas de notes dans toutes les matières, on l'exclut du calcul
                if (!$aDesNotes) {
                    $moyennesParEleve[$inscription->getId()] = [
                        'inscription' => $inscription,
                        'moyennes' => 'NE', // Marquer comme non évalué
                        'rang' => 'NE' // Ajouter rang pour éviter les erreurs de clé
                    ];
                    continue; // Passer à l'élève suivant
                }
        
                foreach ($moyenneEleve as &$detailsMatiere) {
                    $moyenneNoteCours = ($detailsMatiere['nombre_notes_cours'] > 0) ? ($detailsMatiere['somme_notes_cours'] / $detailsMatiere['nombre_notes_cours']) : Null;
                    $moyenneNoteComposition = ($detailsMatiere['nombre_notes_composition'] > 0) ? ($detailsMatiere['somme_notes_composition'] / $detailsMatiere['nombre_notes_composition']) : Null;
                
                    if ($detailsMatiere['nombre_notes_cours'] > 0 || $detailsMatiere['nombre_notes_composition'] > 0) {
                        if ($classe->getFormation()->getCursus()->getNom() == 'collège' or $classe->getFormation()->getCursus()->getNom() == 'lycée') {

                            if ($periode) {
                                $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?:$moyenneNoteCours);
                            }

                            if ($trimestre) {
                                if ($moyenneNoteCours !== null && $moyenneNoteComposition !== null) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteCours + 2 * $moyenneNoteComposition) / 3;
                                }elseif ($moyenneNoteCours !== null && $moyenneNoteComposition == null) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteCours);
                                }elseif ($moyenneNoteCours == null && $moyenneNoteComposition !== null) {
                                    $detailsMatiere['moyenne'] = ($moyenneNoteComposition);
                                }else {
                                    $detailsMatiere['moyenne'] = 0;
                                }
                            }   

                        }elseif($classe->getFormation()->getCursus()->getNom() == 'crèche' or $classe->getFormation()->getCursus()->getNom() == 'maternelle' or $classe->getFormation()->getCursus()->getNom() == 'primaire'){

                            if ($periode) {
                                $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?:$moyenneNoteCours);
                            }

                            if ($trimestre) {
                                $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?:$moyenneNoteCours);
                            }

                        }else{// université
                            if ($periode) {
                                $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?:$moyenneNoteCours);
                            }

                            if ($trimestre) {
                                $detailsMatiere['moyenne'] = ($moyenneNoteCours + 2 * $moyenneNoteComposition) / 3;
                            }                       

                        }
                
                        $detailsMatiere['moyenne_ponderee'] = $detailsMatiere['moyenne'] * $detailsMatiere['coefficient']; // Appliquer le coefficient de la matière
                    }
                }
        
                $moyennesParEleve[$inscription->getId()] = [
                    'inscription' => $inscription,
                    'moyennes' => $moyenneEleve,
                    'rang' => 'NE' // Initialisation pour les élèves non évalués
                ];
            }
        
            // Calcul de la moyenne générale par élève en tenant compte des coefficients des matières
            $sommeMoyennesGenerales = 0;
            $effectifEvalue = 0;
            foreach ($moyennesParEleve as $inscriptionId => $data) {
                if ($data['moyennes'] !== 'NE') {
                    $moyennes = $data['moyennes'];
                    $somme = 0;
                    $coeffTotal = 0;
                    foreach ($moyennes as $detailsMatiereExclus) {
                        // Vérifier si l'élève a une moyenne (non "neval")
                        if ($detailsMatiereExclus['moyenne'] !== 'NE') {
                            // Ajouter la moyenne pondérée et le coefficient au total
                            $somme += $detailsMatiereExclus['moyenne_ponderee'];
                            $coeffTotal += $detailsMatiereExclus['coefficient'];
                        }
                    }
                    if ($coeffTotal > 0) {
                        $moyenneGenerale[$inscriptionId] = $somme / $coeffTotal;
                        $sommeMoyennesGenerales += $moyenneGenerale[$inscriptionId];
                        $effectifEvalue++;
                    } else {
                        $moyenneGenerale[$inscriptionId] = 0; // Ou une autre valeur par défaut
                    }
                } else {
                    $moyenneGenerale[$inscriptionId] = 'NE';
                }
            }
            // dd($moyenneGenerale, $coeffTotal);
        
            // Calcul de la moyenne de la classe
            $moyenneClasse = $effectifEvalue > 0 ? $sommeMoyennesGenerales / $effectifEvalue : 0;
        
            // Trier les élèves par moyenne générale pour attribuer les rangs
            $rangs = [];
            foreach ($moyenneGenerale as $id => $moyenne) {
                if ($moyenne !== 'NE') {
                    $rangs[$id] = $moyenne;
                }
            }
            arsort($rangs); // Trie décroissant
        
            // Attribuer les rangs en gérant les ex-aequo
            $currentRank = 1;
            $lastMoyenne = null;
            $rankOffset = 0;
        
            foreach ($rangs as $id => $moyenne) {
                if ($moyenne === $lastMoyenne) {
                    $moyennesParEleve[$id]['rang'] = $currentRank;
                    $rankOffset++;
                } else {
                    $currentRank += $rankOffset;
                    $rankOffset = 1;
                    $moyennesParEleve[$id]['rang'] = $currentRank;
                    $lastMoyenne = $moyenne;
                }
            }
        
            // Assurer que tous les élèves ont une clé 'rang', même ceux qui sont non évalués
            foreach ($moyennesParEleve as $id => $data) {
                if (!isset($data['rang'])) {
                    $moyennesParEleve[$id]['rang'] = 'NE'; // Valeur par défaut pour non évalué
                }
            }
        
            // Trier les élèves par rang
            uasort($moyennesParEleve, fn($a, $b) => ($a['rang'] === 'NE' ? PHP_INT_MAX : $a['rang']) <=> ($b['rang'] === 'NE' ? PHP_INT_MAX : $b['rang']));
        
            // Calcul des statistiques globales
            $effectifClasse = count($inscriptions);
            $ecartType = 0;
            if ($effectifEvalue > 1) {
                $variance = 0;
                foreach ($moyenneGenerale as $id => $moyenne) {
                    if ($moyenne !== 'NE') {
                        $variance += pow($moyenne - $moyenneClasse, 2);
                    }
                }
                $variance /= $effectifEvalue - 1;
                $ecartType = sqrt($variance);
            }
            // Filtrer les moyennes pour exclure les valeurs 'NE'
            $valeursNumeriques = array_filter($moyenneGenerale, fn($value) => $value !== 'NE');

            // Moyenne la plus élevée et la plus faible
            $moyennePlusElevee = !empty($valeursNumeriques) ? max($valeursNumeriques) : 0;
            $moyennePlusFaible = !empty($valeursNumeriques) ? min($valeursNumeriques) : 0;
        }
        
        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));

        }else{
            $classes = $classeRep->listeDesClassesParEtablissementParPromo($etablissement, $session->get('promo'));
        }

        $responsable = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(2)]) ?:Null;
        $responsable_primaire = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(10)]) ?:Null;
        $responsable_college = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(9)]) ?:Null;
        $responsable_lycee = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(12)]) ?:Null;


        return $this->render('gandaal/administration/pedagogie/pdf/classement.html.twig', [
            'logoPath' => $logoBase64,
            'symbolePath' => $symboleBase64,
            'ministerePath' => $ministereBase64,
            'etablissement' => $etablissement,
            'classes' => $classes,
            'periode' => $periode ?? null,
            'periode_select' => $periode_select ?? null,
            'trimestre' => $trimestre,
            'search_classe' => $classe,
            'moyennesParEleve' => $moyennesParEleve,
            'moyenneGenerale' => $moyenneGenerale,
            'effectifClasse' => $effectifClasse,
            'effectifEvalue' => $effectifEvalue,
            'moyenneClasse' => $moyenneClasse,
            'ecartType' => $ecartType,
            'moyennePlusElevee' => $moyennePlusElevee,
            'moyennePlusFaible' => $moyennePlusFaible,
            'promo' => $session->get('promo'),
            'mois_francais' => $mois_francais,
            'responsable' => $responsable,
            'responsable_primaire' => $responsable_primaire,
            'responsable_college' => $responsable_college,
            'responsable_lycee' => $responsable_lycee,
        ]);
    }

    #[Route('/fiche/note/{etablissement}/{classe}/{matiere}', name: 'app_gandaal_administration_scolarite_pdf_fiche_note')]
    public function ListeEleveClasse(ClasseRepartition $classe, Matiere $matiere, InscriptionRepository $inscriptionRep, DevoirEleveRepository $devoirRep, NoteEleveRepository $noteRep, Etablissement $etablissement, Request $request, FonctionService $fonctionService, SessionInterface $session ): Response
    {     
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/config/'.$etablissement->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $symbolePath = $this->getParameter('kernel.project_dir') . '/public/images/config/symbole.png';
        $symboleBase64 = base64_encode(file_get_contents($symbolePath));
        $ministerePath = $this->getParameter('kernel.project_dir') . '/public/images/config/ministere.jpg';
        $ministereBase64 = base64_encode(file_get_contents($ministerePath));

        $periode_select = $request->get("periode") ?: null;
        $trimestre = $request->get("trimestre") ?: null;

        if ($periode_select) {
            $periode = date("Y") . '-' . $periode_select . '-01';
        } else {
            $periode = null;
        }

        if ($periode) {
            $date = new \DateTime($periode);
            $mois_francais = $fonctionService->getMoisEnFrancais($date);
        }else{
            $mois_francais = Null;

        }

        if ($trimestre) {
            if ($trimestre == 'annuel') {
                $devoirsEleve = $devoirRep->findBy(['matiere' => $matiere, 'classe' => $classe, 'promo' => $session->get('promo')], ['dateDevoir' => 'ASC']);

            }else{
                $devoirsEleve = $devoirRep->findBy(['matiere' => $matiere, 'classe' => $classe, 'periode' => $trimestre, 'promo' => $session->get('promo')], ['dateDevoir' => 'ASC']);
            }
        }else{
            $devoirsEleve = $devoirRep->listeDevoirsParMois($periode_select, $classe, $session->get('promo'), $matiere);
        
        }
        
        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($classe, 'inactif');

        $notesParInscription = [];
        $moyennesParDevoir = []; // Tableau pour stocker les moyennes par devoir

        // Variables pour les moyennes par évaluation
        foreach ($devoirsEleve as $devoir) {
            $notes = $noteRep->findBy(['devoir' => $devoir]);
            $totalNotes = 0;
            $sommeNotes = 0;

            foreach ($notes as $note) {
                $inscriptionId = $note->getInscription()->getId();

                if (!isset($notesParInscription[$inscriptionId])) {
                    $notesParInscription[$inscriptionId] = [];
                }

                // Ajouter la note pour ce devoir spécifique
                $notesParInscription[$inscriptionId][$devoir->getId()] = $note->getValeur();

                // Calculer la somme des notes pour ce devoir
                $sommeNotes += $note->getValeur();
                $totalNotes++;
            }

            // Calculer la moyenne pour ce devoir
            if ($totalNotes > 0) {
                $moyennesParDevoir[$devoir->getNom()] = $sommeNotes / $totalNotes;
            } else {
                $moyennesParDevoir[$devoir->getNom()] = null; // Pas de notes pour ce devoir
            }
        }

        // Calculer la moyenne pour chaque inscription (élève) en séparant les types de devoirs
        foreach ($inscriptions as $inscription) {
            $totalNoteCours = 0;
            $totalComposition = 0;
            $nbNotesCours = 0;
            $nbCompositions = 0;

            if (isset($notesParInscription[$inscription->getId()])) {
                foreach ($notesParInscription[$inscription->getId()] as $devoirId => $note) {
                    // Récupérer le devoir correspondant à l'ID
                    $devoirsCorrespondants = array_filter($devoirsEleve, fn($d) => $d->getId() === $devoirId);
                    // Vérifier si des devoirs ont été trouvés
                    if (!empty($devoirsCorrespondants)) {
                        $devoir = reset($devoirsCorrespondants); // Récupérer le premier élément
                
                        // Distinguer entre "note de cours" et "composition"
                        if ($devoir->getTypeDevoir() === 'note de cours') {
                            $totalNoteCours += $note;
                            $nbNotesCours++;
                        } elseif ($devoir->getTypeDevoir() === 'composition') {
                            $totalComposition += $note;
                            $nbCompositions++;
                        }
                    } else {
                        // Gérer le cas où le devoir n'est pas trouvé, si nécessaire
                        // Par exemple, loguer un avertissement ou lancer une exception
                    }
                }
            }

            // Calcul des moyennes pour chaque type
            $moyenneNoteCours = $nbNotesCours > 0 ? $totalNoteCours / $nbNotesCours : null;
            $moyenneComposition = $nbCompositions > 0 ? $totalComposition / $nbCompositions : null;


            // Calcul de la moyenne finale
            if ($classe->getFormation()->getCursus()->getNom() == 'collège' or $classe->getFormation()->getCursus()->getNom() == 'lycée') {
                if ($moyenneNoteCours !== null && $moyenneComposition !== null) {
                    $moyenneFinale = ($moyenneNoteCours + 2 * $moyenneComposition) / 3;
                    $moyennesParInscription[$inscription->getId()] = round($moyenneFinale, 2);
                }elseif ($moyenneNoteCours !== null && $moyenneComposition == null) {
                    $moyenneFinale = ($moyenneNoteCours );
                    $moyennesParInscription[$inscription->getId()] = round($moyenneFinale, 2);
                }elseif ($moyenneNoteCours == null && $moyenneComposition !== null) {
                    $moyenneFinale = ($moyenneComposition );
                    $moyennesParInscription[$inscription->getId()] = round($moyenneFinale, 2);
                }else {
                    $moyennesParInscription[$inscription->getId()] = null;  // Si pas de notes valides
                }
            }else{

                if ($moyenneNoteCours !== null && $moyenneComposition !== null) {
                    $moyenneFinale = ($moyenneComposition);
                    $moyennesParInscription[$inscription->getId()] = round($moyenneFinale, 2);
                }elseif ($moyenneNoteCours !== null && $moyenneComposition == null) {
                    $moyenneFinale = ($moyenneNoteCours );
                    $moyennesParInscription[$inscription->getId()] = round($moyenneFinale, 2);
                }elseif ($moyenneNoteCours == null && $moyenneComposition !== null) {
                    $moyenneFinale = ($moyenneComposition );
                    $moyennesParInscription[$inscription->getId()] = round($moyenneFinale, 2);
                }else {
                    $moyennesParInscription[$inscription->getId()] = null;  // Si pas de notes valides
                }

            }

            // ici calcul moi la moyenneGeneraleParInscription et envoi à la vue
            // calcul aussi la moyenneGenerale Par devoir et envoi à la vue
        }
        // dd($notesParInscription, $moyennesParInscription);

        // Initialiser les variables pour la moyenne générale de la classe
        $totalMoyenneGenerale = 0;
        $nbElevesAvecMoyenne = 0;

        // Calculer la moyenne générale de la classe
        foreach ($moyennesParInscription as $moyenne) {
            if ($moyenne !== null) {
                $totalMoyenneGenerale += $moyenne;
                $nbElevesAvecMoyenne++;
            }
        }

        // Calculer la moyenne générale de la classe si des élèves ont des moyennes
        $moyenneGeneraleClasse = $nbElevesAvecMoyenne > 0 ? round($totalMoyenneGenerale / $nbElevesAvecMoyenne, 2) : null;

       
        $html = $this->renderView('gandaal/administration/pedagogie/pdf/fiche_note_classe.html.twig', [ 
            'logoPath' => $logoBase64,
            'symbolePath' => $symboleBase64,
            'ministerePath' => $ministereBase64,                    
            'classe' => $classe,
            'matiere' => $matiere,
            'etablissement' => $etablissement,
            'inscriptions' => $inscriptions,
            'devoirs' => $devoirsEleve,
            'notesParInscription' => $notesParInscription,
            'moyennesParInscription' => $moyennesParInscription,
            'moyenneGeneraleClasse' => $moyenneGeneraleClasse, 
            'moyennesParDevoir' => $moyennesParDevoir,
            'mois_francais' => $mois_francais,
            'periode' => $periode ?? null,
            'periode_select' => $periode_select ?? null,
            'trimestre' => $trimestre,
            'search_classe' => $classe,
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename=liste_devoir_'.$matiere->getNom().'_'.$classe->getNom().date("d/m/Y à H:i").'".pdf"',
        ]);
    }


    #[Route('/liste/enseignant/{etablissement}', name: 'app_gandaal_administration_pedagogie_pdf_liste_enseignant')]
    public function listeEnseignant(Etablissement $etablissement, PersonnelActifRepository $personnelActifRep, CursusRepository $cursusRep, EventRepository $eventRep, ClasseRepartitionRepository $classeRep, Request $request, SessionInterface $session ): Response
    {     
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/config/'.$etablissement->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $symbolePath = $this->getParameter('kernel.project_dir') . '/public/images/config/symbole.png';
        $symboleBase64 = base64_encode(file_get_contents($symbolePath));
        $ministerePath = $this->getParameter('kernel.project_dir') . '/public/images/config/ministere.jpg';
        $ministereBase64 = base64_encode(file_get_contents($ministerePath));

        $search_cursus = $request->get("cursus") ? ($request->get('cursus') == 'secondaire' ? 'secondaire' : $cursusRep->find($request->get("cursus"))) : null;

        if ($search_cursus) {
            if ($search_cursus == 'secondaire') {
                $ids = [4, 5];
                $cursus_ids = $cursusRep->listeDesCursusParId($etablissement, $ids);
                $enseignants = $personnelActifRep->listeDesEnseignantsActifParCursusParPromo($cursus_ids, $session->get('promo'));
            }else{

                $enseignants = $personnelActifRep->listeDesEnseignantsActifParCursusParPromo($search_cursus, $session->get('promo'));
            }
        }else{
            $enseignants = $personnelActifRep->listeDesPersonnelsActifParTypeParEtablissementParPromo('enseignant', 'personnel-enseignant', $etablissement, $session->get('promo'));
        }

        $enseignants_data = [];

        foreach ($enseignants as $value) {
            $events_classe = $eventRep->listeDesClassesParEnseignant($value);
            $classesIds = array_map(function($event) {
                return $event['id'];
            }, $events_classe);
        
            // Récupérer les entités Classe pour ces IDs
            $classesByEvents = $classeRep->findBy(['id' => $classesIds]);
            
            
            $classesByResponsable = $classeRep->findBy(['responsable' => $value->getPersonnel(), 'promo' => $session->get('promo')]);

            // Fusionner les deux tableaux de classes
            $classes = array_merge($classesByEvents, $classesByResponsable);

            // Éliminer les doublons, si nécessaire (par exemple, s'il y a des classes en commun)
            $classes = array_unique($classes, SORT_REGULAR);
            ksort($classes);

            
            $enseignants_data[] = [
                'enseignant' => $value,
                'classes' => $classes,
            ];
        }
       
        $html = $this->renderView('gandaal/administration/pedagogie/pdf/liste_enseignant.html.twig', [ 
            'logoPath' => $logoBase64,
            'symbolePath' => $symboleBase64,
            'ministerePath' => $ministereBase64,  
            'etablissement' => $etablissement,
            'search_cursus' => $search_cursus,
            'enseignants' => $enseignants_data,
            'promo' => $session->get('promo')
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename=liste_enseignant'.'".pdf"',
        ]);
    }

    

}
