<?php

namespace App\Controller\Gandaal\Enseignant;

use App\Entity\Event;
use App\Entity\Matiere;
use App\Entity\NoteEleve;
use App\Entity\Enseignant;
use App\Entity\DevoirEleve;
use App\Entity\ControlEleve;
use App\Service\TrieService;
use App\Entity\Etablissement;
use App\Form\DevoirEleveType;
use App\Form\UploadNotesType;
use App\Entity\PersonnelActif;
use App\Service\FonctionService;
use App\Entity\ClasseRepartition;
use App\Entity\DocumentPersonnel;
use App\Form\PersonnelEspaceType;
use App\Entity\DocumentEnseignant;
use App\Form\EnseignantEspaceType;
use App\Repository\UserRepository;
use App\Form\DocumentPersonnelType;
use App\Repository\EventRepository;
use App\Form\DocumentEnseignantType;
use App\Repository\CursusRepository;
use App\Repository\MatiereRepository;
use App\Repository\SalaireRepository;
use App\Repository\FormationRepository;
use App\Repository\NoteEleveRepository;
use App\Repository\PersonnelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DevoirEleveRepository;
use App\Repository\InscriptionRepository;
use Smalot\PdfParser\Parser as PdfParser;
use App\Repository\ControlEleveRepository;
use App\Repository\EtablissementRepository;
use App\Repository\ConfigFonctionRepository;
use App\Repository\PersonnelActifRepository;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClasseRepartitionRepository;
use App\Repository\DocumentEnseignantRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\FonctionnementScolaireRepository;
use App\Repository\PaiementSalairePersonnelRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/gandaal/personnel')]

class EnseignantController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_personnel')]
    public function index(Etablissement $etablissement, SessionInterface $session, SalaireRepository $salaireRep, DocumentEnseignantRepository $documentRep, PersonnelActifRepository $personnelActifRep, Request $request): Response
    {
        $personnelActif = $request->get('personnelActif') ? $personnelActifRep->find($request->get('personnelActif')) : Null;
        if (!$personnelActif) {
            $personnelActif = $personnelActifRep->findOneBy(['personnel' => $this->getUser(), 'promo' => $session->get('promo')]);
        }
        $salaire = $salaireRep->findOneBy(['user' => $personnelActif->getPersonnel(), 'promo' => $session->get('promo')]);
        $documents = $documentRep->findBy(['enseignant' => $personnelActif->getPersonnel()]);
        $personnelActif = $personnelActifRep->findOneBy(['personnel' => $personnelActif->getPersonnel()]);

        return $this->render('gandaal/personnel/index.html.twig', [
            'salaire' => $salaire,
            'documents' => $documents,
            'etablissement' => $etablissement,
            'personnel' => $personnelActif

        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_personnel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PersonnelActif $personnelActif, DocumentEnseignantRepository $documentRep, SalaireRepository $salaireRep, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, SessionInterface $session, Etablissement $etablissement): Response
    {
        if ($personnelActif->getType() == 'enseignant') {
            $form = $this->createForm(EnseignantEspaceType::class, $personnelActif->getPersonnel());
            $form->handleRequest($request);
    
            $document = new DocumentEnseignant();
            $form_document = $this->createForm(DocumentEnseignantType::class, $document);
            $form_document->handleRequest($request);
        }else{

            $form = $this->createForm(PersonnelEspaceType::class, $personnelActif->getPersonnel());
            $form->handleRequest($request);
    
            $document = new DocumentPersonnel();
            $form_document = $this->createForm(DocumentPersonnelType::class, $document);
            $form_document->handleRequest($request);

        }

        if ($form->isSubmitted() && $form->isValid()) {           

            $photo =$form->get("photo")->getData();
            if ($photo) {
                if ($personnelActif->getPersonnel()->getPhoto()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$personnelActif->getPersonnel()->getPhoto();
                    if (file_exists($ancienFichier)) {
                        unlink($ancienFichier);
                    }
                }
                $nomFichier= pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$photo->guessExtension();
                $photo->move($this->getParameter("dossier_personnels"),$nouveauNomFichier);
                $personnelActif->getPersonnel()->setPhoto($nouveauNomFichier);
            }

            $fichier =$form_document->get("nom")->getData();
            if ($fichier) {
                if ($document->getNom()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$document->getNom();
                    if (file_exists($ancienFichier)) {
                        unlink($ancienFichier);
                    }
                }
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_personnels"),$nouveauNomFichier);
                $document->setNom($nouveauNomFichier)
                        ->setType($form_document->get("type")->getData())
                        ->setEnseignant($personnelActif->getPersonnel());

                $entityManager->persist($document);
            }
            $mdp=$form->get("password")->getData();
            if ($mdp) {
                $mdpHashe=$hasher->hashPassword($personnelActif->getPersonnel(), $mdp);
                $personnelActif->getPersonnel()->setPassword($mdpHashe);
            }

            $entityManager->persist($personnelActif->getPersonnel());
            $entityManager->flush();

            $this->addFlash("success", "Enseignant modifié avec succès :)");
            // $referer = $request->headers->get('referer');
            // return $this->redirect($referer);
            return $this->redirectToRoute('app_gandaal_personnel', ['etablissement' => $etablissement->getId(), 'personnelActif' => $personnelActif->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/personnel/edit.html.twig', [
            'personnel' => $personnelActif,
            'form' => $form,
            'form_document' => $form_document,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/calendar/{etablissement}/{personnelActif}', name: 'app_gandaal_personnel_calendar')]
    public function planningPersonnel(Etablissement $etablissement, PersonnelActif $personnelActif,  SessionInterface $session, Request $request): Response
    {
        return $this->render('gandaal/personnel/planning.html.twig', [
            'etablissement' => $etablissement,
            'personnel' => $personnelActif
        ]);
    }

    #[Route('/calendar/api/{etablissement}/{personnelActif}', name: 'app_gandaal_personnel_calendar_api')]
    public function events(Etablissement $etablissement, PersonnelActif $personnelActif, EventRepository $eventRepository, SessionInterface $session,  Request $request): JsonResponse
    {
        $criteria = ['etablissement' => $etablissement];
        
        $criteria['enseignant'] = $personnelActif;
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

    #[Route('/calendar/jour/{etablissement}/{personnelActif}', name: 'app_gandaal_personnel_calendar_jour')]
    public function controlePresence(Etablissement $etablissement, PersonnelActif $personnelActif, ClasseRepartitionRepository $classeRep, EventRepository $eventRep, SessionInterface $session, InscriptionRepository $inscriptionRep, FonctionnementScolaireRepository $fonctionnementRep, ControlEleveRepository $controlEleveRep, TrieService $trieService, Request $request, EntityManagerInterface $em): Response
    {
        $classe = $request->query->get('classe') ? $classeRep->find($request->query->get('classe')) : null;
        $event = $request->query->get('event') ? $eventRep->find($request->query->get('event')) : null;
        // dd($request->query->get('event'));
    
        // Gestion des absences, absences globales, retards et exclusions
        $absence = $request->get('absence');  // Case à cocher d'absence individuelle
        $absenceGlobal = $request->get('absenceGlobal');  // Sélection de l'absence globale (journée, matinée, soirée)
        $retard = $request->get('retard');
        $exclusion = $request->get('exclusion');
        $inscription = $request->get('inscription') ? $inscriptionRep->find($request->get('inscription')) : null;
    
        // Déterminer le type de contrôle en fonction des entrées
        if ($absence) {
            $type = 'absence';  // Absence individuelle via checkbox
        } elseif ($absenceGlobal) {
            $type = 'absence global';  // Absence globale via select
        } elseif ($retard && $retard > 0) {
            $type = 'retard';
        } elseif ($exclusion) {
            $type = 'exclusion';
        } else {
            $type = null;
        }

        $trimestre = $fonctionnementRep->recuperationTrimestre(($event ? $event->getStart() : new \DateTime("now")), $etablissement, $session->get('promo'));
        $trimestre = $trimestre ? $trimestre[0] : array();
        $trimestre = substr($trimestre->getNom(), 0, 1);

        
    
        // Suppression du contrôle si aucune valeur n'est définie
        if (!$absence && !$absenceGlobal && (!$retard || $retard == 0) && !$exclusion) {
            $verif_control = $controlEleveRep->findOneBy([
                'inscription' => $inscription,
                'event' => $event,
                'dateControl' => ($event ? $event->getStart() : new \DateTime("now")),
            ]);
    
            if ($verif_control) {
                $em->remove($verif_control);  // Supprimer le contrôle existant
                $em->flush();
            }
        } elseif ($type) {
            // Supprimer le contrôle existant pour cet élève à cet événement
            $verif_control = $controlEleveRep->findOneBy([
                'inscription' => $inscription,
                'event' => $event,
                'dateControl' => ($event ? $event->getStart() : new \DateTime("now")),
            ]);
    
            if ($verif_control) {
                $em->remove($verif_control);
                $em->flush();
            }

            /* gestion des absences globales */

            if ($type == 'absence global') {  
                $verif_control = $controlEleveRep->findBy([
                    'inscription' => $inscription,
                    'type' => $type,
                    'dateControl' => ($event ? $event->getStart() : new \DateTime("now")),
                ]);

                if ($verif_control) {

                    foreach ($verif_control as $verif) {                    
                        $em->remove($verif);
                        $em->flush();
                    }
                }
        
                if ($event) {
                    /* si l'emploi du temps existe  */
                    $dateJour = ($event->getStart())->format('Y-m-d');
                    /* recupération des évenements de la matinée entre 7h00 et 13h00*/
                    $events = $eventRep->listeEvenementParClasseParTypeParPromoParEtablissementCompriseEntre($classe, $absenceGlobal, $dateJour, $dateJour, $session->get('promo'), $etablissement);
    
                    foreach ($events as $event) {
                        // Créer un nouveau contrôle pour cet élève
                        $controlEleve = new ControlEleve();
                        $controlEleve->setEvent($event)
                            ->setEtablissement($etablissement)
                            ->setPromo($session->get('promo'))
                            ->setSaisiePar($this->getUser())
                            ->setDateSaisie(new \DateTime("now"))
                            ->setDateControl($event ? $event->getStart() : new \DateTime("now"))
                            ->setType($type)
                            ->setInscription($inscription)
                            ->setCommentaire($exclusion ?: ($absenceGlobal ?: $absence))
                            ->setDuree($event ? $event->getDuree() : '')
                            ->setTrimestre($trimestre ?:1);
                        $em->persist($controlEleve);  
                    }
                }else{
                    $controlEleve = new ControlEleve();
                    $controlEleve->setEvent($event)
                        ->setEtablissement($etablissement)
                        ->setPromo($session->get('promo'))
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"))
                        ->setDateControl(new \DateTime("now"))
                        ->setType($type)
                        ->setInscription($inscription)
                        ->setCommentaire($exclusion ?: ($absenceGlobal ?: $absence))
                        ->setDuree(1)
                        ->setTrimestre($trimestre ?:1);
                    $em->persist($controlEleve); 

                }
                $em->flush();
                $referer = $request->headers->get('referer');
                return $this->redirect($referer);
            }
    
            // Créer un nouveau contrôle pour cet élève
            $controlEleve = new ControlEleve();
            
            $controlEleve->setEvent($event)
                ->setEtablissement($etablissement)
                ->setPromo($session->get('promo'))
                ->setSaisiePar($this->getUser())
                ->setDateSaisie(new \DateTime("now"))
                ->setDateControl($event ? $event->getStart() : new \DateTime("now"))
                ->setType($type)
                ->setInscription($inscription)
                ->setCommentaire($exclusion ?: ($absenceGlobal ?: $absence))
                ->setTrimestre($trimestre ?:1);
    
            if ($event) {
                # code...
                // Si le type est "retard", on définit la durée
                if ($type == 'retard') {
                    $controlEleve->setDuree($retard);
                } else {
                    // Pour les autres types, la durée est celle de l'événement
                    $controlEleve->setDuree($event ? $event->getDuree() : '');
                }
            }else{

                if ($type == 'retard') {
                    $controlEleve->setDuree($retard);
                } else {
                    // Pour les autres types, la durée est celle de l'événement
                    $controlEleve->setDuree(1);
                }

            }
    
            $em->persist($controlEleve);  // Persister la nouvelle entité
            $em->flush();
    
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }
   
        $inscriptions = $classe ? $classe->getInscriptions()->toArray() : [];
        $sortedInscriptions = $trieService->trieInscriptions($inscriptions);

        // Récupérer les contrôles pour l'événement
        $controls = $controlEleveRep->findBy(['event' => $event]);

        // Créer un tableau associatif pour faciliter l'accès aux contrôles par inscription
        $controlsByInscription = [];
        foreach ($controls as $control) {
            $controlsByInscription[$control->getInscription()->getId()] = $control;
        }

        // Variables pour les totaux
        $totalAbsences = 0;
        $totalRetards = 0;
        $totalMinutesRetard = 0;
        $totalExclusions = 0;

        // Parcourir les inscriptions pour compter les types d'actions
        foreach ($inscriptions as $inscription) {
            $control = $controlsByInscription[$inscription->getId()] ?? null;

            if ($control) {
                switch ($control->getType()) {
                    case 'absence':
                    case 'absence global':  // Traiter les absences globales comme des absences
                        $totalAbsences++;
                        break;
                    case 'retard':
                        $totalRetards++;
                        $totalMinutesRetard += $control->getDuree();
                        break;
                    case 'exclusion':
                        $totalExclusions++;
                        break;
                }
            }
        }

        $resultat = $eventRep->listeDesClassesParEnseignant($personnelActif);
            
        $classesIds = array_map(function($classe) {
            return $classe['id'];
        }, $resultat);  // $resultat est le tableau retourné par la première requête
        
        $classes = $classeRep->findBy(['id' => $classesIds], ['formation' => 'ASC']);
        if (!$classes) {
            $classes = $classeRep->findBy(['responsable' => $personnelActif->getPersonnel(), 'promo' => $session->get('promo')]);
        }

        // Passer ces données à la vue
        return $this->render('gandaal/personnel/controle_presence.html.twig', [
            'etablissement' => $etablissement,
            'classes' => $classes,
            'classe' => $classe,
            'event' => $event,
            'inscriptions' => $sortedInscriptions,
            'controlsByInscription' => $controlsByInscription, // Nouveau tableau avec les contrôles par inscription
            'totalAbsences' => $totalAbsences,
            'totalRetards' => $totalRetards,
            'totalMinutesRetard' => $totalMinutesRetard,
            'totalExclusions' => $totalExclusions,
            'personnel' => $personnelActif,
        ]);
    }

    

    #[Route('/api/{etablissement}/{personnelActif}', name: 'app_gandaal_personnel_calendar_jour_api')]
    public function controlePresenceEleve(Etablissement $etablissement, PersonnelActif $personnelActif, ClasseRepartitionRepository $classeRep, UserRepository $userRep, EventRepository $eventRepository, SessionInterface $session, Request $request): JsonResponse
    {
        $classe = $request->query->get('classe') ? $classeRep->find($request->query->get('classe')) : null;
        $enseignant = $personnelActif;

        $criteria = ['etablissement' => $etablissement];

        if ($classe) {
            $criteria['classe'] = $classe;
        }

        if ($enseignant) {
            $criteria['enseignant'] = $enseignant;
        }

        $criteria['promo'] = $session->get('promo');

        // dd($enseignant);

        // Si une classe ou un enseignant est sélectionné
        if ($classe || $enseignant) {
            $events = $eventRepository->findBy($criteria);
        } else {
            // Sinon, récupérer un ensemble par défaut d'événements
            $defaultClasse = $classeRep->findOneBy(['promo' => $session->get('promo')]);
            $criteria['classe'] = $defaultClasse;
            $events = $eventRepository->findBy($criteria);
        }

        // Formatage des événements pour l'API
        $formattedEvents = array_map(function ($event) {
            return [
                'id' => $event->getId(),
                'title' => ucwords($event->getTitle()),
                'start' => $event->getStart()->format('Y-m-d\TH:i:s'),
                'end' => $event->getEnd() ? $event->getEnd()->format('Y-m-d\TH:i:s') : null,
                'allDay' => $event->isAllDay(),
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

    #[Route('/salaire/{etablissement}/{personnelActif}', name: 'app_gandaal_personnel_salaire', methods: ['GET'])]
    public function salairePersonnel(PersonnelActif $personnelActif, PaiementSalairePersonnelRepository $paiementRep, SessionInterface $session, Request $request, Etablissement $etablissement): Response
    {
        $periode = date("Y-m-d");
        $periode_select = date("m");
        $type = 'personnel';
        $cursus = 'général';

        $paiements = $paiementRep->findBy(['personnelActif' => $personnelActif, 'etablissement' => $etablissement, 'promo' => $session->get('promo')]);
        
    
        // Grouper les paiements par mode de paiement
        $paiementsParMode = [];
        foreach ($paiements as $paiement) {
            $modePaie = $paiement->getModePaie()->getNom();
            if (!isset($paiementsParMode[$modePaie])) {
                $paiementsParMode[$modePaie] = [];
            }
            $paiementsParMode[$modePaie][] = $paiement;
        }
    
        return $this->render('gandaal/personnel/salaire.html.twig', [
            'paiementsParMode' => $paiementsParMode,
            'etablissement' => $etablissement,
            'promo' => $session->get('promo'),
            'personnel' => $personnelActif,
            'periode' => $periode,
            'periode_select' => $periode_select,
            'type' => $type,
            'cursus' => $cursus,

            
        ]);
    }

    #[Route('/classe/{etablissement}/{personnelActif}', name: 'app_gandaal_personnel_classe', methods: ['GET'])]
    public function classe(PersonnelActif $personnelActif, EventRepository $eventRep, ClasseRepartitionRepository $classeRepartitionRep, CursusRepository $cursusRep,  FormationRepository $formationRep, SessionInterface $session, Etablissement $etablissement): Response
    {
        $cursus = $cursusRep->findBy(['etablissement' => $etablissement]);
        $formations = $formationRep->findBy(['cursus' => $cursus]);
        if ($personnelActif->getType() == 'personnel') {
            $classes = [];
        }else{
            $resultat = $eventRep->listeDesClassesParEnseignant($personnelActif);
            
            $classesIds = array_map(function($classe) {
                return $classe['id'];
            }, $resultat);  // $resultat est le tableau retourné par la première requête
            
            $classes = $classeRepartitionRep->findBy(['id' => $classesIds], ['formation' => 'ASC']);
        }
        // dd($classes);

        // Grouper les classes par niveau
        $classesParFormation = [];
        foreach ($classes as $classe) {
            $formation = $classe->getFormation()->getNom();
            if (!isset($classesParFormation[$formation])) {
                $classesParFormation[$formation] = [];
            }
            $classesParFormation[$formation][] = $classe;
        }
        return $this->render('gandaal/personnel/classe.html.twig', [
            'classe_repartitions' => $classesParFormation,
            'etablissement' => $etablissement,
            'personnel' => $personnelActif,
        ]);
    }

    #[Route('/classe/show/{etablissement}/{classe}/{personnelActif}', name: 'app_gandaal_personnel_classe_show', methods: ['GET'])]
    public function statistiqueClasseShow(
        Etablissement $etablissement, 
        PersonnelActif $personnelActif,
        InscriptionRepository $inscriptionRep, 
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
        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($classe);

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
       
        return $this->render('gandaal/personnel/classe_show.html.twig', [
            'etablissement' => $etablissement,
            'statistiques' => $statistiquesParClasse,
            'inscriptionsGroupByType' => $inscriptionsGroupByType,
            'classe' => $classe,
            'promo' => $session->get('promo'),
            'personnel' => $personnelActif,
        ]);
    }

    #[Route('/devoir/{etablissement}/{classe}/{personnelActif}', name: 'app_gandaal_personnel_devoir', methods: ['GET'])]
    public function devoirPersonnel(DevoirEleveRepository $devoirRep, Etablissement $etablissement, PersonnelActif $personnelActif, ClasseRepartition $classe, Request $request, SessionInterface $session, ClasseRepartitionRepository $classeRep, MatiereRepository $matiereRep): Response
    {
        if ($request->get('classe')) {
            $classe = $classeRep->find($request->get('classe'));
            $session->set('session_classe_devoir', $classe);
        }
        

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
                $devoirs = $devoirRep->listeDevoirsAnnuel($classe, $session->get('promo'), $personnelActif->getPersonnel());
            }else{

                $devoirs = $devoirRep->findBy(['enseignant' => $personnelActif->getPersonnel(), 'classe' => $classe, 'periode' => $trimestre, 'promo' => $session->get('promo')]);
            }
        }else{
            $devoirs = $devoirRep->findBy(['enseignant' => $personnelActif->getPersonnel(), 'classe' => $classe, 'promo' => $session->get('promo')]);
        
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
    
        return $this->render('gandaal/personnel/devoir/devoir.html.twig', [
            'devoirs' => $groupedDevoirs,
            'etablissement' => $etablissement,
            'periode' => $periode ?? null,
            'periode_select' => $periode_select ?? null,
            'trimestre' => $trimestre,
            'search_classe' => $classe,
            'search_matiere' => array(),
            'personnel' => $personnelActif
        ]);
    }

    #[Route('/devoir/show/{devoirEleve}/{personnelActif}/{etablissement}', name: 'app_gandaal_personnel_devoir_show', methods: ['GET', 'POST'])]
    public function showDevoir(DevoirEleve $devoirEleve, PersonnelActif $personnelActif, Etablissement $etablissement, EtablissementRepository $etablissementRep, NoteEleveRepository $noteRep, InscriptionRepository $inscriptionRep,  Request $request, EntityManagerInterface $entityManager): Response
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
            return $this->redirectToRoute('app_gandaal_personnel_devoir_show', ['devoirEleve' => $devoirEleve->getId(), 'personnelActif' => $personnelActif->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        // gestion de la suppression des notes
        if ($request->get('note')) {
            $note = $noteRep->find($request->get('note'));
            $entityManager->remove($note);
            $entityManager->flush();
            $this->addFlash('success', 'la note est supprimée avec succès :)');
            return $this->redirectToRoute('app_gandaal_personnel_devoir_show', ['devoirEleve' => $devoirEleve->getId(), 'personnelActif' => $personnelActif->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        $inscriptions = $inscriptionRep->listeDesElevesInscritsParClasse($devoirEleve->getClasse(), 'inactif');
        $notes = $noteRep->findBy(['devoir' => $devoirEleve]);
        // Créer un tableau associatif pour lier les inscriptions aux notes
        $notesParInscription = [];
        foreach ($notes as $note) {
            $notesParInscription[$note->getInscription()->getId()] = $note;
        }
        // dd($devoirEleve->getClasse()->getNom());
        return $this->render('gandaal/personnel/devoir/devoir_show.html.twig', [
            'form' => $form->createView(),
            'devoir_eleve' => $devoirEleve,
            'etablissement' => $etablissement,
            'inscriptions' => $inscriptions,
            'notesParInscription' => $notesParInscription,
            'personnel' => $personnelActif,
        ]);
    }

    #[Route('/show/matiere/classe/{matiere}/{classe}/{personnelActif}/{etablissement}', name: 'app_gandaal_personnel_devoir_matiere_classe_show', methods: ['GET', 'POST'])]
    public function showDevoirEleveMatiere(Matiere $matiere, ClasseRepartition $classe, PersonnelActif $personnelActif, Etablissement $etablissement, DevoirEleveRepository $devoirRep, NoteEleveRepository $noteRep, InscriptionRepository $inscriptionRep, PersonnelRepository $personnelRep, ConfigFonctionRepository $fonctionRep, SessionInterface $session, Request $request, FonctionService $fonctionService): Response
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
        return $this->render('gandaal/personnel/devoir/show_devoir_classe_matiere.html.twig', [
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
            'personnel' => $personnelActif,
        ]);

    }

    #[Route('/devoir/edit/{id}/{personnelActif}/{etablissement}', name: 'app_gandaal_personnel_devoir_edit', methods: ['GET', 'POST'])]
    public function editDevoirPersonnel(Request $request, DevoirEleve $devoirEleve, PersonnelActif $personnelActif, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DevoirEleveType::class, $devoirEleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devoirEleve->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));
            $entityManager->flush();

            $this->addFlash('success', 'Devoir modifié avec succès :)');
            return $this->redirectToRoute('app_gandaal_personnel_devoir_show', ['devoirEleve' => $devoirEleve->getId(), 'personnelActif' => $personnelActif->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/personnel/devoir/edit.html.twig', [
            'devoir_eleve' => $devoirEleve,
            'form' => $form,
            'etablissement' => $etablissement,
            'personnel' => $personnelActif
        ]);
    }


    #[Route('/delete/{id}/{personnelActif}/{etablissement}', name: 'app_gandaal_personnel_devoir_delete', methods: ['POST'])]
    public function deleteDevoirPersonnel(Request $request, DevoirEleve $devoirEleve, PersonnelActif $personnelActif, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$devoirEleve->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($devoirEleve);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_personnel_devoir', ['etablissement' => $etablissement->getId(), 'classe' => $devoirEleve->getClasse()->getId(), 'personnelActif' => $personnelActif->getId()], Response::HTTP_SEE_OTHER);
    }
}
