<?php

namespace App\Controller\Gandaal\Administration\Pedagogie;

use App\Entity\NoteEleve;
use App\Entity\DevoirEleve;
use App\Entity\Etablissement;
use App\Form\DevoirEleveType;
use App\Form\UploadNotesType;
use App\Service\FonctionService;
use App\Entity\ClasseRepartition;
use App\Entity\Matiere;
use App\Repository\MatiereRepository;
use App\Repository\NoteEleveRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DevoirEleveRepository;
use App\Repository\InscriptionRepository;
use Smalot\PdfParser\Parser as PdfParser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClasseRepartitionRepository;
use App\Repository\ConfigFonctionRepository;
use App\Repository\EventRepository;
use App\Repository\PersonnelActifRepository;
use App\Repository\PersonnelRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\EventRegistry;

#[Route('/gandaal/administration/pedagogie/devoir/eleve')]
class DevoirEleveController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_pedagogie_devoir_eleve_index', methods: ['GET'])]
    public function index(DevoirEleveRepository $devoirRep, Etablissement $etablissement, Request $request, SessionInterface $session, ClasseRepartitionRepository $classeRep, MatiereRepository $matiereRep): Response
    {
        if ($request->get('classe')) {
            $classe = $classeRep->find($request->get('classe'));
            $session->set('session_classe_devoir', $classe);
        }
        $classe = $session->get('session_classe_devoir');

        if ($request->get('matiere')) {
            $matiere = $matiereRep->find($request->get('matiere'));
            $session->set('session_matiere_devoir', $matiere);
        }
        $matiere = $session->get('session_matiere_devoir');

        $matieres = $matiereRep->findBy(['formation' => $classe ? $classe->getFormation() : ''], ['nom' => 'ASC', 'categorie' => 'ASC']);
        
        $periode_select = $request->get("periode") ?: null;
        $trimestre = $request->get("trimestre") ?: null;
    
        if ($periode_select) {
            $periode = date("Y") . '-' . $periode_select . '-01';
        } else {
            $periode = null;
        }

        if ($trimestre) {
            if ($trimestre == 'annuel') {
                $devoirs = $devoirRep->listeDevoirsAnnuel($classe, $session->get('promo'));
            }else{

                $devoirs = $devoirRep->findBy(['classe' => $classe, 'periode' => $trimestre, 'promo' => $session->get('promo')]);
            }
        }else{
            $devoirs = $devoirRep->listeDevoirsParMois($periode_select, $classe, $session->get('promo'));
        
        }

        // Tableau pour stocker les devoirs regroupés
        $groupedDevoirs = [];

        // Boucle pour grouper par matière
        foreach ($devoirs as $devoir) {
            $matiereNom = $devoir->getMatiere()->getNom();

            // Initialiser le tableau si la matière n'existe pas encore
            if (!isset($groupedDevoirs[$matiereNom])) {
                $groupedDevoirs[$matiereNom] = [];
            }

            // Ajouter le devoir à la matière correspondante
            $groupedDevoirs[$matiereNom][] = $devoir;
        }

        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));

        }else{
            $classes = $classeRep->listeDesClassesParEtablissementParPromo($etablissement, $session->get('promo'));
        }
    
        return $this->render('gandaal/administration/pedagogie/devoir_eleve/index.html.twig', [
            'devoirs' => $groupedDevoirs,
            'etablissement' => $etablissement,
            'classes' => $classes,
            'matieres' => $matieres,
            'periode' => $periode ?? null,
            'periode_select' => $periode_select ?? null,
            'trimestre' => $trimestre,
            'search_classe' => $classe,
            'search_matiere' => $matiere,
        ]);
    }

    #[Route('/new/{etablissement}/{classe}', name: 'app_gandaal_administration_pedagogie_devoir_eleve_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Etablissement $etablissement, EventRepository $eventRep, PersonnelActifRepository $personnelActifRep, ClasseRepartition $classe, MatiereRepository $matiereRep, DevoirEleveRepository $devoirRep, SessionInterface $session, FonctionService $fonctionService, EntityManagerInterface $entityManager): Response
    {  
        if ($request->get('ajout_devoir')) {
            $type = $request->get('type');
            $trimestre = $request->get('trimestre');
            $periodes = $request->get('periodes');
            $matieres = $request->get('matieres');
            $promo = $session->get('promo');

            foreach ($periodes as $periode) {
                if ((int)$periode >= 9 && (int)$periode <= 12) {
                    $anneePromo = $promo - 1;
                    $periode_select = $anneePromo . '-' . $periode  . '-25';
    
                }else{
                    $periode_select = date("Y") . '-' . $periode  . '-25';
    
                }

                $periode_select = new \DateTime($periode_select);


                foreach ($matieres as $matiere) {
                    $matiere_entity = $matiereRep->find($matiere);

                    $verif_composition = $devoirRep->findOneBy(['classe' => $classe, 'matiere' => $matiere_entity, 'periode' => $trimestre, 'typeDevoir' => 'composition', 'promo' => $session->get('promo') ]);

                    if ($verif_composition) {
                        $this->addFlash('warning', 'Une composition à cette periode existe déjà pour '. $matiere_entity->getNom());
                    }else {

                        $verif_devoir = $devoirRep->findOneBy(['classe' => $classe, 'matiere' => $matiere_entity, 'dateDevoir' => $periode_select, 'periode' => $trimestre, 'typeDevoir' => $type, 'promo' => $session->get('promo') ]);
                        if ($verif_devoir) {
                            $this->addFlash('warning', 'ce devoir est déjà crée pour '. $matiere_entity->getNom());
                        }else {
                            $event = $eventRep->findOneBy(['classe' => $classe, 'matiere' => $matiere_entity, 'etablissement' => $etablissement, 'promo' => $session->get('promo')]);
                            if ($event) {
                                $enseignant = $event->getEnseignant()->getPersonnel();
                            }else{
                                if ($classe->getFormation()->getCursus()->getNom() == 'crèche' or $classe->getFormation()->getCursus()->getNom() == 'maternelle' or $classe->getFormation()->getCursus()->getNom() == 'primaire') {
                                    $enseignant = $classe->getResponsable();
                                }else{
                                    $enseignant = $this->getUser();
                                }
                            }
                            // dd($enseignant);
                            $nom='évaluation '.$fonctionService->getMoisEnFrancais($periode_select) ;
                            $devoirEleve = new DevoirEleve();
                            $devoirEleve->setNom($nom)
                                ->setClasse($classe)
                                ->setMatiere($matiere_entity)
                                ->setEnseignant($enseignant)
                                ->setTypeDevoir($type)
                                ->setCoef(1)
                                ->setPeriode($trimestre)
                                ->setDateDevoir($periode_select)
                                ->setDateSaisie(new \DateTime())
                                ->setSaisiePar($this->getUser())
                                ->setPromo($session->get('promo'));
                            $entityManager->persist($devoirEleve);

                            $this->addFlash('success', 'Dévoir de '. $matiere_entity->getNom().' ajouté avec succès :) ');


                        }
                    }

                $entityManager->flush();


                }
            }
            return $this->redirectToRoute('app_gandaal_administration_pedagogie_devoir_eleve_index', ['etablissement' => $etablissement->getId(), 'classe' => $classe->getId(), 'trimestre' => $trimestre], Response::HTTP_SEE_OTHER);
        }

        $matieres = $matiereRep->findBy(['formation' => $classe ? $classe->getFormation() : ''], ['nom' => 'ASC', 'categorie' => 'ASC']);

        return $this->render('gandaal/administration/pedagogie/devoir_eleve/new.html.twig', [
            'etablissement' => $etablissement,
            'classe' => $classe,
            'matieres' => $matieres,

        ]);
    }

    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_devoir_eleve_show', methods: ['GET', 'POST'])]
    public function show(DevoirEleve $devoirEleve, NoteEleveRepository $noteRep, InscriptionRepository $inscriptionRep, Etablissement $etablissement, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UploadNotesType::class);        
        $form->handleRequest($request);

        // traitementd de la saisie des notes par pdf ou image
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
            $lines = preg_split('/\r\n|\r|\n/', $text); // Séparer le texte en lignes

            // Tableau pour stocker les informations des élèves
            $students = [];
            $ignoreLines = true; // Variable pour ignorer les premières lignes inutiles
            $stopProcessing = false; // Variable pour arrêter une fois la partie élèves terminée
            foreach ($lines as $line) {
                // Nettoyage de chaque ligne et suppression des lignes vides
                $line = trim($line);

                // Ignorer les lignes jusqu'à ce qu'on trouve l'en-tête des élèves
                if ($ignoreLines) {
                    if (strpos($line, 'N°') !== false && strpos($line, 'Identifiant') !== false) {
                        $ignoreLines = false; // Arrêter d'ignorer à partir de l'en-tête
                    }
                    continue;
                }

                // Vérifier si on est à la fin de la section élèves
                if ($stopProcessing) {
                    break; // Arrêter le traitement si on a fini avec les élèves
                }

                // Si la ligne contient des informations comme "Document imprimé par", on arrête
                if (strpos($line, 'Document imprimé par') !== false || strpos($line, 'Fondation') !== false) {
                    $stopProcessing = true;
                    continue;
                }
                $line = preg_replace('/[^\PC\s]/u', '', $line); // Supprime les caractères invisibles et de contrôle 
                $line = trim(preg_replace('/\s+/', ' ', $line)); // Nettoie les espaces
                dump($line);

                // L'expression régulière modifiée pour rendre le score optionnel
                if (preg_match('/^(\d+)\s+(\d+)\s+([A-Z0-9]+)\s+([A-Za-z\s]+)(?:\s+(\d+))?$/', $line, $matches)) {
                    $students[] = [
                        'numero' => $matches[1], // Le numéro de ligne
                        'matricule' => $matches[2], // Le matricule de l'élève
                        'nom' => trim($matches[4]), // Le nom complet de l'élève, nettoyé des espaces superflus
                        'note' => isset($matches[5]) ? $matches[5] : null, // Note ou null si absente
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

        // traitement de la saisie de note manuelle

        if ($request->get('inscription_ids')) {
            // Récupérer les données du formulaire
            $notes = $request->get('notes', []);
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

        // gestion de la suppression des notes
        if ($request->get('note')) {
            $note = $noteRep->find($request->get('note'));
            $entityManager->remove($note);
            $entityManager->flush();
            $this->addFlash('success', 'la note est supprimée avec succès :)');
            return $this->redirectToRoute('app_gandaal_administration_pedagogie_devoir_eleve_show', ['id' => $devoirEleve->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($devoirEleve->getClasse(), 'inactif');
        
        $notes = $noteRep->findBy(['devoir' => $devoirEleve]);
        // Créer un tableau associatif pour lier les inscriptions aux notes
        $notesParInscription = [];
        foreach ($notes as $note) {
            $notesParInscription[$note->getInscription()->getId()] = $note;
        }

        return $this->render('gandaal/administration/pedagogie/devoir_eleve/show.html.twig', [
            'form' => $form->createView(),
            'devoir_eleve' => $devoirEleve,
            'etablissement' => $etablissement,
            'inscriptions' => $inscriptions,
            'notesParInscription' => $notesParInscription, // Transmettre les notes à la vue
        ]);
    }

    #[Route('/show/matiere/classe/{matiere}/{classe}/{etablissement}', name: 'app_gandaal_administration_pedagogie_devoir_eleve_matiere_classe_show', methods: ['GET', 'POST'])]
    public function showDevoirEleveMatiere(Matiere $matiere, ClasseRepartition $classe, DevoirEleveRepository $devoirRep, NoteEleveRepository $noteRep, InscriptionRepository $inscriptionRep, Etablissement $etablissement, PersonnelRepository $personnelRep, ConfigFonctionRepository $fonctionRep, SessionInterface $session, Request $request, FonctionService $fonctionService): Response
    {
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

        $responsable = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(2)]) ?:Null;
        $responsable_primaire = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(10)]) ?:Null;
        $responsable_college = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(9)]) ?:Null;
        $responsable_lycee = $personnelRep->findOneBy(['fonction' => $fonctionRep->find(12)]) ?:Null;

        // Passer la moyenne générale à la vue
        return $this->render('gandaal/administration/pedagogie/devoir_eleve/show_devoir_classe_matiere.html.twig', [
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
            'responsable' => $responsable,
            'responsable_primaire' => $responsable_primaire,
            'responsable_college' => $responsable_college,
            'responsable_lycee' => $responsable_lycee,
        ]);

    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_devoir_eleve_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DevoirEleve $devoirEleve, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DevoirEleveType::class, $devoirEleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devoirEleve->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));
            $entityManager->flush();

            $this->addFlash('success', 'Devoir modifié avec succès :)');
            return $this->redirectToRoute('app_gandaal_administration_pedagogie_devoir_eleve_show', ['id' => $devoirEleve->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/pedagogie/devoir_eleve/edit.html.twig', [
            'devoir_eleve' => $devoirEleve,
            'form' => $form,
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/delete/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_devoir_eleve_delete', methods: ['POST'])]
    public function delete(Request $request, DevoirEleve $devoirEleve, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$devoirEleve->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($devoirEleve);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_administration_pedagogie_devoir_eleve_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
