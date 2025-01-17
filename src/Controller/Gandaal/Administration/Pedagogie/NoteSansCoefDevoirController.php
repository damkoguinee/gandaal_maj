<?php

namespace App\Controller\Gandaal\Administration\Pedagogie;

use IntlDateFormatter;
use App\Entity\Matiere;
use App\Form\MatiereType;
use App\Entity\Etablissement;
use App\Repository\CursusRepository;
use App\Repository\MatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\NoteEleveRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DevoirEleveRepository;
use App\Repository\InscriptionRepository;
use App\Repository\NiveauClasseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieMatiereRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClasseRepartitionRepository;
use App\Repository\ControlEleveRepository;
use App\Service\FonctionService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gandaal/administration/pedagogie/general/note')]
class NoteSansCoefDevoirController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_general_note', methods: ['GET'])]
public function index(
    MatiereRepository $matiereRep, 
    ClasseRepartitionRepository $classeRep, 
    DevoirEleveRepository $devoirRep, 
    NoteEleveRepository $noteRep, 
    InscriptionRepository $inscriptionRep, 
    Request $request, 
    CursusRepository $cursusRep,  
    FormationRepository $formationRep, 
    SessionInterface $session, 
    FonctionService $fonctionService,
    ControlEleveRepository $controlEleveRep,
    Etablissement $etablissement
): Response {
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
    $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($classe);

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
                    'moyenne' => 'neval', // Initialisé à 'neval' par défaut
                    'moyenne_ponderee' => 0
                ];
            }
    
            foreach ($notesEleve as $noteEleve) {
                $matiere = $noteEleve->getDevoir()->getMatiere();
                $typeDevoir = $noteEleve->getDevoir()->getTypeDevoir();
    
                if ($typeDevoir === 'note de cours') {
                    $moyenneEleve[$matiere->getId()]['somme_notes_cours'] += $noteEleve->getValeur();
                    $moyenneEleve[$matiere->getId()]['nombre_notes_cours']++;
                    $aDesNotes = true; // L'élève a au moins une note
                } elseif ($typeDevoir === 'composition') {
                    $moyenneEleve[$matiere->getId()]['somme_notes_composition'] += $noteEleve->getValeur();
                    $moyenneEleve[$matiere->getId()]['nombre_notes_composition']++;
                    $aDesNotes = true; // L'élève a au moins une note
                }
            }
    
            // Si l'élève n'a pas de notes dans toutes les matières, on l'exclut du calcul
            if (!$aDesNotes) {
                $moyennesParEleve[$inscription->getId()] = [
                    'inscription' => $inscription,
                    'moyennes' => 'neval', // Marquer comme non évalué
                    'rang' => 'neval' // Ajouter rang pour éviter les erreurs de clé
                ];
                continue; // Passer à l'élève suivant
            }
    
            foreach ($moyenneEleve as &$detailsMatiere) {
                $moyenneNoteCours = ($detailsMatiere['nombre_notes_cours'] > 0) ? ($detailsMatiere['somme_notes_cours'] / $detailsMatiere['nombre_notes_cours']) : 0;
                $moyenneNoteComposition = ($detailsMatiere['nombre_notes_composition'] > 0) ? ($detailsMatiere['somme_notes_composition'] / $detailsMatiere['nombre_notes_composition']) : 0;
    
                // Calcul de la moyenne pondérée en tenant compte des coefficients
                if ($detailsMatiere['nombre_notes_cours'] > 0 || $detailsMatiere['nombre_notes_composition'] > 0) {
                    if ($classe->getFormation()->getCursus()->getNom() == 'collège' or $classe->getFormation()->getCursus()->getNom() == 'lycée') {

                        if ($periode) {
                            $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?:$moyenneNoteCours);
                        }

                        if ($trimestre) {
                            $detailsMatiere['moyenne'] = ($moyenneNoteCours + 2 * $moyenneNoteComposition) / 3;
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
                    $detailsMatiere['moyenne_ponderee'] = $detailsMatiere['moyenne'] * $detailsMatiere['coefficient'];
                }
            }
    
            $moyennesParEleve[$inscription->getId()] = [
                'inscription' => $inscription,
                'moyennes' => $moyenneEleve,
                'rang' => 'neval' // Initialisation pour les élèves non évalués
            ];
        }
    
        // Calcul des moyennes par matière
        $moyennesParMatiere = [];
        $moyennesParMatiereCoef = [];
        $countMatiereCoef = 0;
    
        foreach ($matieres as $matiere) {
            $sommeMatiere = 0;
            $coefficientTotal = 0;
    
            foreach ($moyennesParEleve as $data) {
                // Vérification si l'élève a composé sur la matière (note de cours ou composition)
                if ($data['moyennes'] !== 'neval') {
                    $moyenneMatiere = $data['moyennes'][$matiere->getId()]['moyenne'] ?? 0;
                    $aCompose = $data['moyennes'][$matiere->getId()]['nombre_notes_cours'] > 0 || $data['moyennes'][$matiere->getId()]['nombre_notes_composition'] > 0;
    
                    // Si l'élève a effectivement composé sur la matière, inclure la moyenne dans le calcul
                    if ($aCompose && $moyenneMatiere > 0) {
                        $sommeMatiere += $moyenneMatiere * $matiere->getCoef(); // Utiliser le coefficient
                        $coefficientTotal += $matiere->getCoef(); // Accumuler les coefficients
                    }
                }
            }
    
            // Stocker la moyenne par matière uniquement si des élèves ont composé
            $moyennesParMatiere[$matiere->getId()] = ($coefficientTotal > 0) ? $sommeMatiere / $coefficientTotal : 0;
    
            $moyennesParMatiereCoef[] = $moyennesParMatiere[$matiere->getId()] * $matiere->getCoef();
            $countMatiereCoef += $matiere->getCoef();
        }
    
        // Calcul de la moyenne générale des matières
        $sommeMoyennesGeneralesParMatiere = array_sum($moyennesParMatiereCoef);
        $moyenneGeneraleDesMatieres = ($countMatiereCoef > 0) ? $sommeMoyennesGeneralesParMatiere / $countMatiereCoef : 0;
    
        // Calcul de la moyenne générale par élève en tenant compte des coefficients des matières
        $sommeMoyennesGenerales = 0;
        $effectifEvalue = 0;
        foreach ($moyennesParEleve as $inscriptionId => $data) {
            if ($data['moyennes'] !== 'neval') {
                $moyennes = $data['moyennes'];
                $somme = 0;
                $coeffTotal = 0;
                foreach ($moyennes as $detailsMatiere) {
                    $somme += $detailsMatiere['moyenne_ponderee'];
                    $coeffTotal += $detailsMatiere['coefficient'];
                }
                if ($coeffTotal > 0) {
                    $moyenneGenerale[$inscriptionId] = $somme / $coeffTotal;
                    $sommeMoyennesGenerales += $moyenneGenerale[$inscriptionId];
                    $effectifEvalue++;
                } else {
                    $moyenneGenerale[$inscriptionId] = 0; // Ou une autre valeur par défaut
                }
            } else {
                $moyenneGenerale[$inscriptionId] = 'neval';
            }
        }
    
        // Calcul de la moyenne de la classe
        $moyenneClasse = $effectifEvalue > 0 ? $sommeMoyennesGenerales / $effectifEvalue : 0;
    
        // Trier les élèves par moyenne générale pour attribuer les rangs
        $rangs = [];
        foreach ($moyenneGenerale as $id => $moyenne) {
            if ($moyenne !== 'neval') {
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
                $moyennesParEleve[$id]['rang'] = 'neval'; // Valeur par défaut pour non évalué
            }
        }
    
        // Trier les élèves par rang
        uasort($moyennesParEleve, fn($a, $b) => ($a['rang'] === 'neval' ? PHP_INT_MAX : $a['rang']) <=> ($b['rang'] === 'neval' ? PHP_INT_MAX : $b['rang']));
    
        // Calcul des statistiques globales
        $effectifClasse = count($inscriptions);
        $ecartType = 0;
        if ($effectifEvalue > 1) {
            $variance = 0;
            foreach ($moyenneGenerale as $id => $moyenne) {
                if ($moyenne !== 'neval') {
                    $variance += pow($moyenne - $moyenneClasse, 2);
                }
            }
            $variance /= $effectifEvalue - 1;
            $ecartType = sqrt($variance);
        }
        // Filtrer les moyennes pour exclure les valeurs 'neval'
        $valeursNumeriques = array_filter($moyenneGenerale, fn($value) => $value !== 'neval');

        // Moyenne la plus élevée et la plus faible
        $moyennePlusElevee = !empty($valeursNumeriques) ? max($valeursNumeriques) : 0;
        $moyennePlusFaible = !empty($valeursNumeriques) ? min($valeursNumeriques) : 0;
    }
    
    if ($session->get('session_cursus')) {
        $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));

    }else{
        $classes = $classeRep->listeDesClassesParEtablissementParPromo($etablissement, $session->get('promo'));
    }
    $controles = $controlEleveRep->listeDesControlesParClasseGroupe($classe, $periode_select, $trimestre) ;

    return $this->render('gandaal/administration/pedagogie/general/note/index.html.twig', [
        'etablissement' => $etablissement,
        'classes' => $classes,
        'periode' => $periode ?? null,
        'periode_select' => $periode_select ?? null,
        'trimestre' => $trimestre,
        'search_classe' => $classe,
        'matieres' => $matieres,
        'moyennesParEleve' => $moyennesParEleve,
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
        'controles' => $controles,
    ]);
}

    
    


    #[Route('/new/{etablissement}', name: 'app_gandaal_general_note_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CursusRepository $cursusRep,  FormationRepository $formationRep, Etablissement $etablissement): Response
    {
        $matiere = new Matiere();
        $cursus = $cursusRep->findBy(['etablissement' => $etablissement]);
        
        $formations = $formationRep->findBy(['cursus' => $cursus]);
        $form = $this->createForm(MatiereType::class, $matiere, ['formations' => $formations]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($matiere);
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_general_note_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/pedagogie/note/new.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_general_note_show', methods: ['GET'])]
    public function show(Matiere $matiere, Etablissement $etablissement): Response
    {
        return $this->render('gandaal/administration/pedagogie/note/show.html.twig', [
            'matiere' => $matiere,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_general_note_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Matiere $matiere, CursusRepository $cursusRep,  FormationRepository $formationRep, EntityManagerInterface $entityManager, Etablissement $etablissement): Response
    {
        $cursus = $cursusRep->findBy(['etablissement' => $etablissement]);
        
        $formations = $formationRep->findBy(['cursus' => $cursus]);
        $form = $this->createForm(MatiereType::class, $matiere, ['formations' => $formations]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_general_note_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/pedagogie/note/edit.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/delete/{id}', name: 'app_gandaal_general_note_delete', methods: ['POST'])]
    public function delete(Request $request, Matiere $matiere, EntityManagerInterface $entityManager, Etablissement $etablissement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matiere->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($matiere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_general_note_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
