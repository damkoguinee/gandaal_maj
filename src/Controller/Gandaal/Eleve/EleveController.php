<?php

namespace App\Controller\Gandaal\Eleve;


use App\Entity\Matiere;
use App\Entity\NoteEleve;
use App\Entity\DevoirEleve;
use App\Entity\Inscription;
use App\Entity\ControlEleve;
use App\Service\TrieService;
use App\Entity\Etablissement;
use App\Form\DevoirEleveType;
use App\Form\UploadNotesType;
use App\Service\FonctionService;
use App\Entity\ClasseRepartition;
use App\Entity\Eleve;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\CursusRepository;
use App\Repository\MatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\NoteEleveRepository;
use App\Repository\PersonnelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DevoirEleveRepository;
use App\Repository\InscriptionRepository;
use Smalot\PdfParser\Parser as PdfParser;
use App\Repository\ConfigCaisseRepository;
use App\Repository\ConfigDeviseRepository;
use App\Repository\ControlEleveRepository;
use App\Repository\DocumentEleveRepository;
use App\Repository\EtablissementRepository;
use App\Repository\PaiementEleveRepository;
use App\Repository\ConfigFonctionRepository;
use App\Repository\FraisScolariteRepository;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Repository\TranchePaiementRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\FraisInscriptionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClasseRepartitionRepository;
use App\Repository\ConfigModePaiementRepository;
use App\Repository\InscriptionActiviteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\FonctionnementScolaireRepository;
use App\Repository\PaiementSalairePersonnelRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gandaal/eleve')]

class EleveController extends AbstractController
{
    #[Route('/{etablissement}/{inscription}', name: 'app_gandaal_eleve')]
    public function index(Etablissement $etablissement, Inscription $inscription, SessionInterface $session, PaiementEleveRepository $paiementRep, InscriptionActiviteRepository $inscriptionActiviteRep, DocumentEleveRepository $documentRep, Request $request): Response
    {
        $documents = $documentRep->findBy(['eleve' => $inscription->getEleve()->getId()]);
        $cumulPaiements = $paiementRep->cumulPaiementEleveGroupeParType($inscription, $session->get('promo'));
        $activites = $inscriptionActiviteRep->findBy(['eleve' => $inscription->getEleve(), 'promo' => $session->get('promo')]);
        return $this->render('gandaal/eleve/index.html.twig', [
            'inscription' => $inscription,
            'etablissement' => $etablissement,
            'documents' => $documents,
            'promo' => $session->get('promo'),
            'cumulPaiements' => $cumulPaiements,
            'activites' => $activites

        ]);
    }

    #[Route('/parent', name: 'app_gandaal_eleve_parent')]
    public function homeParent(SessionInterface $session, PaiementEleveRepository $paiementRep, InscriptionActiviteRepository $inscriptionActiviteRep, DocumentEleveRepository $documentRep): Response
    {
        $etablissement = $this->getUser()->getEtablissement();
        
        return $this->render('gandaal/eleve/accueil_parent.html.twig', [
            'etablissement' => $etablissement,            
            'promo' => $session->get('promo')

        ]);
    }

    #[Route('/parent/eleve/{eleve}', name: 'app_gandaal_eleve_parent_inscription')]
    public function eleveInscription(Eleve $eleve, SessionInterface $session, InscriptionRepository $inscriptionRep): Response
    {
        $inscription = $inscriptionRep->findOneBy(['eleve' => $eleve, 'promo' => $session->get('promo')]);
        return $this->redirectToRoute('app_gandaal_eleve', ['etablissement' => $this->getUser()->getEtablissement()->getId(), 'inscription' => $inscription->getId()], Response::HTTP_SEE_OTHER);
        
    }

    #[Route('/calendar/{etablissement}/{inscription}', name: 'app_gandaal_eleve_calendar')]
    public function planningPersonnel(Etablissement $etablissement, Inscription $inscription,  SessionInterface $session, Request $request): Response
    {
        return $this->render('gandaal/eleve/planning.html.twig', [
            'etablissement' => $etablissement,
            'inscription' => $inscription
        ]);
    }

    #[Route('/calendar/api/{etablissement}/{inscription}', name: 'app_gandaal_eleve_calendar_api')]
    public function events(Etablissement $etablissement, Inscription $inscription, EventRepository $eventRepository, SessionInterface $session,  Request $request): JsonResponse
    {
        $criteria = ['etablissement' => $etablissement];
        
        $criteria['classe'] = $inscription->getClasse();
        $criteria['promo'] = $session->get('promo');
        
        $events = $eventRepository->findBy($criteria);

        // Formatage des événements pour l'API
        $formattedEvents = array_map(function ($event) {
            return [
                'id' => $event->getId(),
                'title' => ucwords($event->getTitle()),
                'start' => $event->getStart()->format('Y-m-d\TH:i:s'),
                'end' => $event->getEnd() ? $event->getEnd()->format('Y-m-d\TH:i:s') : null,
                'allDay' => $event->isAllDay(),
                // 'url' => 'edit/'.$event->getId(),
                'className' => $event->getClassName(),
                'backgroundColor' => $event->getBackgroundColor(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),
                'classe' => $event->getClasse() ? $event->getClasse()->getNom() : null,
                'enseignant' => $event->getEnseignant() ? $event->getEnseignant()->getPersonnel()->getNomComplet() : null,
                'matiere' => $event->getMatiere() ? ucwords($event->getMatiere()->getNom()) : null,
            ];
        }, $events);

        return new JsonResponse($formattedEvents);
    }


    #[Route('/paiement/{etablissement}/{inscription}', name: 'app_gandaal_eleve_paiement', methods: ['GET', 'POST'])]
    public function paiement(Etablissement $etablissement, Inscription $inscription, SessionInterface $session, FraisScolariteRepository $fraisScolRep, FraisInscriptionRepository $fraisInsRep, PaiementEleveRepository $paiementRep, TranchePaiementRepository $tranchePaieRep, ConfigCaisseRepository $caisseRep, ConfigModePaiementRepository $modePaieRep, ConfigDeviseRepository $deviseRep): Response
    {       
        $remiseIns = $inscription->getRemiseInscription() ? $inscription->getRemiseInscription() : 0;
        $remiseScolarite = $inscription->getRemiseScolarite() ? $inscription->getRemiseScolarite() : 0;
        $classe = $inscription->getClasse();

        $frais = $fraisScolRep->findBy(['formation' => $classe->getFormation(), 'promo' => $session->get('promo')]);

        $cursus = $classe->getFormation()->getCursus();

        $totalScolarite = $fraisScolRep->montantTotalFraisScolariteParFormation($classe->getFormation(), $session->get('promo'));

        $totalScolarite = $totalScolarite * (1 - ($remiseScolarite / 100));

        $fraisIns = $fraisInsRep->findOneBy(['cursus' => $cursus, 'description' => $inscription->getType(), 'promo' => $session->get('promo')]); 
        $fraisInscription = $fraisIns->getMontant() * (1 - ($remiseIns / 100));

        $scolarite_annuel = $totalScolarite + $fraisInscription;

        $reste_scolarite = $paiementRep->resteScolariteEleve($inscription, $session->get('promo'), $frais, $remiseScolarite/100);

        $reste_inscription = $fraisInscription - $paiementRep->paiementInscription($inscription, $session->get('promo'));
        
        $historiques = $paiementRep->findBy(['inscription' => $inscription, 'promo' => $session->get('promo')], ['id' => 'DESC']);
        $cumulPaiements = $paiementRep->cumulPaiementEleveGroupeParType($inscription, $session->get('promo'));
       
        
        return $this->render('gandaal/eleve/paiement.html.twig', [
            'etablissement' => $etablissement,
            'inscription' => $inscription,           
            'historiques' => $historiques,
            'cumulPaiements' => $cumulPaiements,
            'reste_scolarite' => $reste_scolarite,
            'reste_inscription' => $reste_inscription,
            'scolarite_annuel' => $scolarite_annuel,
            
        ]);
    }

    #[Route('/devoir/{etablissement}/{inscription}', name: 'app_gandaal_eleve_devoir', methods: ['GET'])]
    public function devoirPersonnel(DevoirEleveRepository $devoirRep, Etablissement $etablissement, Inscription $inscription, Request $request, SessionInterface $session, MatiereRepository $matiereRep): Response
    {
               

        if ($request->get('matiere')) {
            $matiere = $matiereRep->find($request->get('matiere'));
            $session->set('session_matiere_devoir', $matiere);
        }
        
        $periode_select = $request->get("periode") ?: null;
        $trimestre = $request->get("trimestre") ?: null;
    
        if ($periode_select) {
            $periode = date("Y") . '-' . $periode_select . '-01';
        } else {
            $periode = null;
        }

        if ($trimestre) {
            if ($trimestre == 'annuel') {
                $devoirs = $devoirRep->listeDevoirsAnnuel($inscription->getClasse(), $session->get('promo'));
            }else{

                $devoirs = $devoirRep->findBy(['classe' => $inscription->getClasse(), 'periode' => $trimestre, 'promo' => $session->get('promo')]);
            }
        }else{
            $devoirs = $devoirRep->findBy(['classe' => $inscription->getClasse(), 'promo' => $session->get('promo')]);
        
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
    
        return $this->render('gandaal/eleve/devoir/devoir.html.twig', [
            'devoirs' => $groupedDevoirs,
            'etablissement' => $etablissement,
            'periode' => $periode ?? null,
            'periode_select' => $periode_select ?? null,
            'trimestre' => $trimestre,
            'search_classe' => $inscription->getClasse(),
            'search_matiere' => array(),
            'inscription' => $inscription
        ]);
    }

    #[Route('/devoir/show/{devoirEleve}/{inscription}/{etablissement}', name: 'app_gandaal_eleve_devoir_show', methods: ['GET', 'POST'])]
    public function showDevoir(DevoirEleve $devoirEleve, Inscription $inscription, Etablissement $etablissement, EtablissementRepository $etablissementRep, NoteEleveRepository $noteRep, InscriptionRepository $inscriptionRep,  Request $request, EntityManagerInterface $entityManager): Response
    {
        $inscriptions = $inscriptionRep->findBy(['id' => $inscription]);
        $notes = $noteRep->findBy(['devoir' => $devoirEleve]);
        // Créer un tableau associatif pour lier les inscriptions aux notes
        $notesParInscription = [];
        foreach ($notes as $note) {
            $notesParInscription[$note->getInscription()->getId()] = $note;
        }
        // dd($devoirEleve->getClasse()->getNom());
        return $this->render('gandaal/eleve/devoir/devoir_show.html.twig', [
            'devoir_eleve' => $devoirEleve,
            'etablissement' => $etablissement,
            'inscriptions' => $inscriptions,
            'notesParInscription' => $notesParInscription,
            'inscription' => $inscription,
        ]);
    }

    #[Route('/show/matiere/classe/{matiere}/{inscription}/{etablissement}', name: 'app_gandaal_eleve_devoir_matiere_classe_show', methods: ['GET', 'POST'])]
    public function showDevoirEleveMatiere(Matiere $matiere, Inscription $inscription, Etablissement $etablissement, DevoirEleveRepository $devoirRep, NoteEleveRepository $noteRep, InscriptionRepository $inscriptionRep, PersonnelRepository $personnelRep, ConfigFonctionRepository $fonctionRep, SessionInterface $session, Request $request, FonctionService $fonctionService): Response
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
                $devoirsEleve = $devoirRep->findBy(['matiere' => $matiere, 'classe' => $inscription->getClasse(), 'promo' => $session->get('promo')], ['dateDevoir' => 'ASC']);

            }else{
                $devoirsEleve = $devoirRep->findBy(['matiere' => $matiere, 'classe' => $inscription->getClasse(), 'periode' => $trimestre, 'promo' => $session->get('promo')], ['dateDevoir' => 'ASC']);
            }
        }else{
            $devoirsEleve = $devoirRep->listeDevoirsParMois($periode_select, $inscription->getClasse(), $session->get('promo'), $matiere);
        
        }


        $inscriptions = $inscriptionRep->findBy(['id' => $inscription]);

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
            if ($inscription->getClasse()->getFormation()->getCursus()->getNom() == 'collège' or $inscription->getClasse()->getFormation()->getCursus()->getNom() == 'lycée') {
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
        return $this->render('gandaal/eleve/devoir/show_devoir_classe_matiere.html.twig', [
            'classe' => $inscription->getClasse(),
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
            'search_classe' => $inscription->getClasse(),
            'responsable' => $responsable,
            'responsable_primaire' => $responsable_primaire,
            'responsable_college' => $responsable_college,
            'responsable_lycee' => $responsable_lycee,
            'inscription' => $inscription,
        ]);

    }

    #[Route('/devoir/edit/{id}/{inscription}/{etablissement}', name: 'app_gandaal_eleve_devoir_edit', methods: ['GET', 'POST'])]
    public function editDevoirPersonnel(Request $request, DevoirEleve $devoirEleve, Inscription $inscription, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DevoirEleveType::class, $devoirEleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devoirEleve->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));
            $entityManager->flush();

            $this->addFlash('success', 'Devoir modifié avec succès :)');
            return $this->redirectToRoute('app_gandaal_eleve_devoir_show', ['devoirEleve' => $devoirEleve->getId(), 'inscription' => $inscription->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/eleve/devoir/edit.html.twig', [
            'devoir_eleve' => $devoirEleve,
            'form' => $form,
            'etablissement' => $etablissement,
            'inscription' => $inscription
        ]);
    }


    #[Route('/delete/{id}/{inscription}/{etablissement}', name: 'app_gandaal_eleve_devoir_delete', methods: ['POST'])]
    public function deleteDevoirPersonnel(Request $request, DevoirEleve $devoirEleve, Inscription $inscription, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$devoirEleve->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($devoirEleve);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_eleve_devoir', ['etablissement' => $etablissement->getId(), 'classe' => $devoirEleve->getClasse()->getId(), 'inscription' => $inscription->getId()], Response::HTTP_SEE_OTHER);
    }
}
