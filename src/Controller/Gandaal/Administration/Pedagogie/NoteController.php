<?php

namespace App\Controller\Gandaal\Administration\Pedagogie;

use IntlDateFormatter;
use App\Entity\Matiere;
use App\Entity\NoteEleve;
use App\Form\MatiereType;
use App\Entity\DevoirEleve;
use App\Entity\Etablissement;
use App\Form\UploadNotesType;
use App\Service\FonctionService;
use App\Repository\CursusRepository;
use App\Repository\MatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\NoteEleveRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DevoirEleveRepository;
use App\Repository\InscriptionRepository;
use App\Repository\ControlEleveRepository;
use App\Repository\NiveauClasseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieMatiereRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClasseRepartitionRepository;
use App\Repository\ConfigFonctionRepository;
use App\Repository\ConfigurationModuleRepository;
use App\Repository\PersonnelRepository;
use ContainerP2ePJxV\getConfigurationLogicielTypeService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Smalot\PdfParser\Parser as PdfParser;
use thiagoalessio\TesseractOCR\TesseractOCR;

#[Route('/gandaal/administration/pedagogie/general/note')]
class NoteController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_pedagogie_general_note', methods: ['GET'])]
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
        Etablissement $etablissement,
        PersonnelRepository $personnelRep,
        ConfigFonctionRepository $fonctionRep,
        ConfigurationModuleRepository $moduleRep,

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
        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($classe, 'inactif');

        // Récupération des matières
        $matieres = $matiereRep->findBy(['formation' => $classe ? $classe->getFormation() : ''], ['categorie' => 'ASC', 'nom' => 'ASC']);
        $matieresIndex = [];
        foreach ($matieres as $matiere) {
            $matieresIndex[$matiere->getId()] = $matiere;
        }
        // dd($matieresIndex);
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
                    $moyenneNoteCours = ($detailsMatiere['nombre_notes_cours'] > 0) ? ($detailsMatiere['somme_notes_cours'] / $detailsMatiere['nombre_notes_cours']) : NULL;
                    $moyenneNoteComposition = ($detailsMatiere['nombre_notes_composition'] > 0) ? ($detailsMatiere['somme_notes_composition'] / $detailsMatiere['nombre_notes_composition']) : NULL;
                
                    if ($detailsMatiere['nombre_notes_cours'] > 0 || $detailsMatiere['nombre_notes_composition'] > 0) {
                        if ($classe->getFormation()->getCursus()->getNom() == 'collège' or $classe->getFormation()->getCursus()->getNom() == 'lycée') {

                            if ($periode) {
                                $detailsMatiere['moyenne'] = ($moyenneNoteComposition ?:$moyenneNoteCours);
                            }

                            if ($trimestre) {
                                // dd($moyenneNoteCours,  $moyenneNoteComposition);
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
        
            // Calcul des moyennes par matière
            $moyennesParMatiere = [];
            $moyennesParMatiereCoef = [];
            $countMatiereCoef = 0;
        
            foreach ($matieres as $matiere) {
                $sommeMatiere = 0;
                $coefficientTotal = 0;
                // dd($moyennesParEleve);
                foreach ($moyennesParEleve as $data) {
                    // Vérification si l'élève a composé sur la matière (note de cours ou composition)
                    if ($data['moyennes'] !== 'NE') {
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
                if ($moyennesParMatiere[$matiere->getId()] > 0) {
                    $moyennesParMatiereCoef[] = $moyennesParMatiere[$matiere->getId()] * $matiere->getCoef();

                    $countMatiereCoef += $matiere->getCoef();
                }
            }
            // dd($countMatiereCoef);

        
            // Calcul de la moyenne générale des matières
            $sommeMoyennesGeneralesParMatiere = array_sum($moyennesParMatiereCoef);
            $moyenneGeneraleDesMatieres = ($countMatiereCoef > 0) ? $sommeMoyennesGeneralesParMatiere / $countMatiereCoef : 0;
        
            $sommeMoyennesGenerales = 0;
            $effectifEvalue = 0;
            $moyenneGenerale = [];
// dd($moyennesParEleve);

            // Parcourir chaque élève
            
            foreach ($moyennesParEleve as $inscriptionId => $data) {
                $moyennes = $data['moyennes'];
                $somme = 0;
                $coeffTotal = 0;
                // Vérifier que $moyennes est un tableau avant d'utiliser foreach
                if (is_array($moyennes)) {
                    // Parcourir les matières de l'élève
                    // dump($moyennes);
                    foreach ($moyennes as $detailsMatiereExclus) {
                        // Vérifier si l'élève a une moyenne (non "neval")
                        if ($detailsMatiereExclus['moyenne'] !== 'NE') {
                            // Ajouter la moyenne pondérée et le coefficient au total
                            $somme += $detailsMatiereExclus['moyenne_ponderee'];
                            $coeffTotal += $detailsMatiereExclus['coefficient'];
                        }
                    }
                    // dd($moyennes);

                    // Si l'élève a été évalué dans au moins une matière
                    if ($coeffTotal > 0) {
                        // Calculer la moyenne générale pondérée pour cet élève
                        $moyenneGenerale[$inscriptionId] = $somme / $coeffTotal;
                        $sommeMoyennesGenerales += $moyenneGenerale[$inscriptionId];
                        $effectifEvalue++;
                    } else {
                        // Si l'élève n'a aucune évaluation, définir la moyenne générale à "neval"
                        $moyenneGenerale[$inscriptionId] = 'NE';
                    }
                } else {
                    // Si $moyennes n'est pas un tableau, marquer l'élève comme non évalué
                    $moyenneGenerale[$inscriptionId] = 'NE';
                }
            }
            // Moyenne générale de la classe
            if ($effectifEvalue > 0) {
                $moyenneClasse = $sommeMoyennesGenerales / $effectifEvalue;
                // dd($effectifEvalue);
            } else {
                $moyenneClasse = 'NE'; // Cas où aucun élève n'est évalué
            }

        // dd($moyennesParEleve);
            // Calcul de la moyenne de la classe
            $moyenneClasse = $effectifEvalue > 0 ? $sommeMoyennesGenerales / $effectifEvalue : 0;

            // dd($moyenneGeneraleDesMatieres, $moyenneClasse);
        
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
            // dd($moyennesParEleve);
            // Assurer que tous les élèves ont une clé 'rang', même ceux qui sont non évalués
            foreach ($moyennesParEleve as $id => $data) {
                if (!isset($data['rang'])) {
                    $moyennesParEleve[$id]['rang'] = 'NE'; // Valeur par défaut pour non évalué
                }
            }
            // dd($moyennesParEleve);
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
        $controles = $controlEleveRep->listeDesControlesParClasseGroupe($classe, $periode_select, $trimestre) ;
        // dd($moyennesParEleve);

        $responsable = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(2)]) ?:Null;
        $responsable_primaire = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(10)]) ?:Null;
        $responsable_college = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(9)]) ?:Null;
        $responsable_lycee = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(12)]) ?:Null;

        // dd($classe);
        if ($classe) {
            $module = $moduleRep->moduleParCursusParPeriodeParEtablissement($classe->getFormation()->getCursus(), $periode_select, $etablissement);

        }else{
            $module = NULL;
        }
        // dd($module);

        // dd($moyennesParEleve);
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
            'responsable' => $responsable,
            'responsable_primaire' => $responsable_primaire,
            'responsable_college' => $responsable_college,
            'responsable_lycee' => $responsable_lycee,
            'module' => $module,
        ]);
    }

    #[Route('/new/{etablissement}', name: 'app_gandaal_administration_pedagogie_general_note_new', methods: ['GET', 'POST'])]
    public function new(Etablissement $etablissement, DevoirEleveRepository $devoirRep,  Request $request, EntityManagerInterface $entityManager, NoteEleveRepository $noteRep, InscriptionRepository $inscriptionRep): Response
    {
        // Récupérer les données du formulaire
        $notes = $request->get('notes', []);
        $devoirEleve = $devoirRep->find($request->get('devoirEleve'));
        $inscriptionIds = $request->get('inscription_ids', []);
        // Traiter chaque inscription et mettre à jour la note pour ce devoir
        foreach ($inscriptionIds as $inscriptionId) {
            $inscription = $inscriptionRep->find($inscriptionId);
            if ($inscription) {
                $note = $notes[$inscriptionId] ?? null;
                if ($note !== null && $note >= 0 && $note <= 20) {
                    // Associez la note au devoir
                    $verif_note = $noteRep->findOneBy(['devoir' => $devoirEleve, 'inscription' => $inscription]);
                    if ($verif_note) {
                        $verif_note->setValeur($note)
                            ->setDateSaisie(new \DateTime())
                            ->setSaisiePar($this->getUser());
                        $entityManager->persist($verif_note);
                    }else{

                        $noteEleve = new NoteEleve();
                        $noteEleve->setInscription($inscription)
                                ->setValeur($note)
                                ->setDevoir($devoirEleve)
                                ->setDateSaisie(new \DateTime())
                                ->setSaisiePar($this->getUser());   
                        $entityManager->persist($noteEleve);
                    }
                }
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Les notes ont été enregistrées avec succès.');
        return $this->redirectToRoute('app_gandaal_administration_pedagogie_devoir_eleve_show', ['id' => $devoirEleve->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }

    
    #[Route('/new/upload/{etablissement}', name: 'app_gandaal_administration_pedagogie_general_note_new_upload', methods: ['GET', 'POST'])]
    public function uploadNote(Request $request, Etablissement $etablissement)
    {
        $form = $this->createForm(UploadNotesType::class);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('document')->getData();
            $text = '';
        
            // Vérifiez le type de fichier
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        
            if (in_array($extension, ['pdf'])) {
                // Utilisez PdfParser pour extraire le texte
                $pdfParser = new PdfParser();
                $pdf = $pdfParser->parseFile($file->getPathname());
                $text = $pdf->getText();
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                // Utilisez TesseractOCR pour extraire le texte
                $tesseract = new TesseractOCR($file->getPathname());
                $text = $tesseract->run();
            }
        
            // Traitement du texte extrait
            $lines = preg_split('/\r\n|\r|\n/', $text); // Sépare le texte en lignes
            $students = [];
       
            foreach ($lines as $line) {
                // Nettoyez la ligne et ignorez les lignes vides
                $line = trim($line);
                if (empty($line) || strpos($line, 'N°') !== false || strpos($line, 'Matricule') !== false) {
                    continue; // Ignore les en-têtes
                }
            
                // Utilisez une expression régulière pour extraire les informations
                // Assurez-vous de capturer la note après le téléphone
                if (preg_match('/^(\d+)\s+([A-Z0-9]+)\s+(.+?)\s+(\d+)\s+(\d+)$/', $line, $matches)) {
                    $students[] = [
                        'numero' => $matches[1],
                        'matricule' => $matches[2],
                        'nom' => trim($matches[3]), // Retirer les espaces superflus
                        'telephone' => $matches[4],
                        'note' => $matches[5], // La note est maintenant capturée correctement
                    ];
                }
            }
            
            

            dd($students);
            // Enregistrez les données dans la base de données
            foreach ($students as $student) {
                $note = $student['note'];

                if ($note !== null && $note >= 0 && $note <= 20) {
                    // Associez la note au devoir
                    $verif_note = $noteRep->findOneBy(['devoir' => $devoirEleve, 'inscription' => $inscription]);
                    if ($verif_note) {
                        $verif_note->setValeur($note)
                            ->setDateSaisie(new \DateTime())
                            ->setSaisiePar($this->getUser());
                        $entityManager->persist($verif_note);
                    }else{

                        $noteEleve = new NoteEleve();
                        $noteEleve->setInscription($inscription)
                                ->setValeur($note)
                                ->setDevoir($devoirEleve)
                                ->setDateSaisie(new \DateTime())
                                ->setSaisiePar($this->getUser());   
                        $entityManager->persist($noteEleve);
                    }
                }
                
            }
            $entityManager->flush();
        
            return $this->redirectToRoute('success_page');
        }
        

        return $this->render('gandaal/administration/pedagogie/devoir_eleve/upload_note.html.twig', [
            'form' => $form->createView(),
            'etablissement' => $etablissement,
        ]);
    }


    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_general_note_show', methods: ['GET'])]
    public function show(Matiere $matiere, Etablissement $etablissement): Response
    {
        return $this->render('gandaal/administration/pedagogie/note/show.html.twig', [
            'matiere' => $matiere,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_general_note_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Matiere $matiere, CursusRepository $cursusRep,  FormationRepository $formationRep, EntityManagerInterface $entityManager, Etablissement $etablissement): Response
    {
        $cursus = $cursusRep->findBy(['etablissement' => $etablissement]);
        
        $formations = $formationRep->findBy(['cursus' => $cursus]);
        $form = $this->createForm(MatiereType::class, $matiere, ['formations' => $formations]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_administration_pedagogie_general_note_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/pedagogie/note/edit.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/delete/{id}', name: 'app_gandaal_administration_pedagogie_general_note_delete', methods: ['POST'])]
    public function delete(Request $request, Matiere $matiere, EntityManagerInterface $entityManager, Etablissement $etablissement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matiere->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($matiere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_administration_pedagogie_general_note_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
