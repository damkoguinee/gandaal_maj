<?php
namespace App\Controller\Gandaal\Administration\Pedagogie\Admin;

use App\Entity\Event;
use App\Entity\ControlEleve;
use App\Service\TrieService;
use App\Entity\Etablissement;
use App\Form\ControlEleveType;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Entity\HistoriqueSuppression;
use App\Repository\MatiereRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InscriptionRepository;
use phpDocumentor\Reflection\Types\Null_;
use App\Repository\ControlEleveRepository;
use App\Repository\EtablissementRepository;
use App\Repository\HeureTravailleRepository;
use App\Repository\PersonnelActifRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ClasseRepartitionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\PaiementSalairePersonnelRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gandaal/administration/pedagogie/admin/gestion/absence')]
class GestionAbsenceController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_gestion_absence')]
    public function index(Etablissement $etablissement,  ClasseRepartitionRepository $classeRep, ControlEleveRepository $controlRep, SessionInterface $session, Request $request): Response
    {
        $search = $request->get('search') ?:NULL;
        $type = $request->get('type') ?:NULL;
        $periode = $request->get('periode') ?:date('Y-m-d');
        $classe = $request->query->get('classe') ? $classeRep->find($request->query->get('classe')) : null;
        if ($session->get('session_cursus')) {
            $classes = $classeRep->listeDesClassesParCursusParPromo($session->get('session_cursus'), $session->get('promo'));

            $controles = $controlRep->listeDesControlesParPromoParClasse($session->get('promo'), $classes, $search, $periode, $type);

        }else{

            $controles = $controlRep->listeDesControlesParPromoParEtablissement($session->get('promo'), $etablissement, $search, $periode, $type);
        }
        
        return $this->render('gandaal/administration/pedagogie/admin/gestion_absence/index.html.twig', [
            'etablissement' => $etablissement,
            'classes' => $classeRep->listeDesClassesParEtablissementParPromo($etablissement, $request->getSession()->get('promo')),
            'classe' => $classe,
            'controles' => $controles,
            'promo' => $session->get('promo'),
            'periode' => $periode,
            'type' => $type,
        ]);
    }

    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_gestion_absence_show', methods: ['GET', 'POST'])]
    public function show(ControlEleve $controlEleve, ControlEleveRepository $controlRep, InscriptionRepository $inscriptionRep, SessionInterface $session, Request $request, TrieService $trieService, ClasseRepartitionRepository $classeRep, Etablissement $etablissement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ControlEleveType::class, $controlEleve, [] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateAbsence = $controlEleve->getDateControl();
            $controls = $controlRep->findBy(['inscription' => $controlEleve->getInscription(), 'dateControl' => $dateAbsence, 'promo' => $session->get('promo'), 'etablissement' => $etablissement]);
            foreach ($controls as $key => $control) {
                // dd($control);
                $control->setEtat('justifié')
                        ->setCommentaireJustificatif($form->getViewData()->getCommentaireJustificatif())
                        ->setDateJustificatif($form->getViewData()->getDateJustificatif())
                        ->setSaisieJustificatif($this->getUser());
                $em->persist($control);
            }
            $justificatif =$form->get("justificatif")->getData();
    
            if ($justificatif) {
                if ($controlEleve->getJustificatif()) {
                    $ancienJustificatif=$this->getParameter("dossier_eleves_justificatifs")."/".$controlEleve->getJustificatif();
                    if (file_exists($ancienJustificatif)) {
                        unlink($ancienJustificatif);
                    }
                }
                $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                $nouveauNomJustificatif .="_".uniqid();
                $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                $justificatif->move($this->getParameter("dossier_eleves_justificatifs"),$nouveauNomJustificatif);
                $controlEleve->setJustificatif($nouveauNomJustificatif);
                $em->persist($controlEleve);
            }

            $em->flush();
            $this->addFlash("success", "justificatif validé avec succès :)");

            return $this->redirectToRoute('app_gandaal_administration_pedagogie_admin_gestion_absence_show', ['id' => $controlEleve->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);

        }

        // Récupération des contrôles de l'élève (absences, retards, exclusions)
        $controls = $controlRep->findBy([
            'inscription' => $controlEleve->getInscription(),
            'promo' => $session->get('promo'),
            'etablissement' => $etablissement
        ]);
        // Initialisation des compteurs
        $absences = [
            'total' => 0,
            'justifie' => 0,
            'non_justifie' => 0
        ];

        $retards = [
            'total' => 0,
            'justifie' => 0,
            'non_justifie' => 0
        ];

        $exclusions = [
            'total' => 0,
            'justifie' => 0,
            'non_justifie' => 0
        ];

        // Catégorisation des contrôles et calcul des totaux
        foreach ($controls as $control) {
            
            switch ($control->getType()) {
                case 'absence':
                case 'absence global':
                    $absences['total']++;
                    if ($control->getEtat() === 'justifié') {
                        $absences['justifie']++;
                    } else {
                        $absences['non_justifie']++;
                    }
                    break;

                case 'retard':
                    $retards['total']++;
                    if ($control->getEtat() === 'justifié') {
                        $retards['justifie']++;
                    } else {
                        $retards['non_justifie']++;
                    }
                    break;

                case 'exclusion':
                    $exclusions['total']++;
                    if ($control->getEtat() === 'justifié') {
                        $exclusions['justifie']++;
                    } else {
                        $exclusions['non_justifie']++;
                    }
                    break;
            }
        }

        // Récapitulatif par matière
        $recapParMatiere = [];

        if ($control->getEvent()) {
            foreach ($controls as $control) {
                $matiereNom = $control->getEvent()->getMatiere()->getNom();
    
                if (!isset($recapParMatiere[$matiereNom])) {
                    $recapParMatiere[$matiereNom] = [
                        'absences' => 0,
                        'retards' => 0,
                        'exclusions' => 0
                    ];
                }
    
                switch ($control->getType()) {
                    case 'absence':
                    case 'absence global':
                        $recapParMatiere[$matiereNom]['absences']++;
                        break;
                    case 'retard':
                        $recapParMatiere[$matiereNom]['retards']++;
                        break;
                    case 'exclusion':
                        $recapParMatiere[$matiereNom]['exclusions']++;
                        break;
                }
            }
        }


        // Rendu de la vue avec les variables nécessaires
        return $this->render('gandaal/administration/pedagogie/admin/gestion_absence/show.html.twig', [
            'controlEleve' => $controlEleve,
            'form' => $form->createView(),
            'etablissement' => $etablissement,
            'promo' => $session->get('promo'),
            'controls' => $controls,  // Les contrôles bruts
            'absences' => $absences,  // Totaux des absences
            'retards' => $retards,    // Totaux des retards
            'exclusions' => $exclusions, // Totaux des exclusions
            'recapParMatiere' => $recapParMatiere // Récapitulatif par matière
        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_gestion_absence_edit', methods: ['GET', 'POST'])]
    public function edit(ControlEleve $controlEleve, InscriptionRepository $inscriptionRep, SessionInterface $session, Request $request, Filesystem $filesystem, ControlEleveRepository $controlRep, Etablissement $etablissement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ControlEleveType::class, $controlEleve, [] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($controlEleve->getEtat() == 'non justifié') {
                /* on efface le motif ainsi que le justificatif */
                $justificatif = $controlEleve->getJustificatif();
                $pdfPath = $this->getParameter("dossier_eleves_justificatifs") . '/' . $justificatif;
                // Si le chemin du justificatif existe, supprimez également le fichier
                if ($justificatif && $filesystem->exists($pdfPath)) {
                    $filesystem->remove($pdfPath);
                }

                $dateAbsence = $controlEleve->getDateControl();
                $controls = $controlRep->findBy(['inscription' => $controlEleve->getInscription(), 'dateControl' => $dateAbsence, 'promo' => $session->get('promo'), 'etablissement' => $etablissement]);
                foreach ($controls as $key => $control) {
                    $control->setCommentaireJustificatif(NUll)
                        ->setJustificatif(NULL)
                        ->setEtat('non justifié')
                        ->setSaisieJustificatif($this->getUser());
                    $em->persist($control);
                }

                
                $em->flush();
                $this->addFlash("success", "justificatif annulé avec succès :)");
            }else{

                $dateAbsence = $controlEleve->getDateControl();
                $controls = $controlRep->findBy(['inscription' => $controlEleve->getInscription(), 'dateControl' => $dateAbsence, 'promo' => $session->get('promo'), 'etablissement' => $etablissement]);
                foreach ($controls as $key => $control) {
                    // dd($control);
                    $control->setEtat('justifié')
                            ->setCommentaireJustificatif($form->getViewData()->getCommentaireJustificatif())
                            ->setDateJustificatif($form->getViewData()->getDateJustificatif())
                            ->setSaisieJustificatif($this->getUser());
                    $em->persist($control);
                }

                $justificatif =$form->get("justificatif")->getData();
                if ($justificatif) {
                    if ($controlEleve->getJustificatif()) {
                        $ancienJustificatif=$this->getParameter("dossier_eleves_justificatifs")."/".$controlEleve->getJustificatif();
                        if (file_exists($ancienJustificatif)) {
                            unlink($ancienJustificatif);
                        }
                    }
                    $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                    $nouveauNomJustificatif .="_".uniqid();
                    $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                    $justificatif->move($this->getParameter("dossier_eleves_justificatifs"),$nouveauNomJustificatif);
                    $controlEleve->setJustificatif($nouveauNomJustificatif);
                    $em->persist($controlEleve);

    
                }
                $em->flush();
                $this->addFlash("success", "justificatif validé avec succès :)");
            }

            return $this->redirectToRoute('app_gandaal_administration_pedagogie_admin_gestion_absence_show', ['id' => $controlEleve->getId(), 'etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);

        }
        
        return $this->render('gandaal/administration/pedagogie/admin/gestion_absence/edit.html.twig', [
            'controlEleve' => $controlEleve,
            'form' => $form,
            'etablissement' => $etablissement,
            'promo' => $session->get('promo'),
        ]);
    }

    #[Route('/delete/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_gestion_absence_delete', methods: ['POST'])]
    public function delete(Request $request, ControlEleve $controlEleve, EntityManagerInterface $entityManager, Filesystem $filesystem, etablissement $etablissement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$controlEleve->getId(), $request->request->get('_token'))) {
            $justificatif = $controlEleve->getJustificatif();
            $pdfPath = $this->getParameter("dossier_eleves_justificatifs") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }

            
            $entityManager->remove($controlEleve);
            $entityManager->flush();

            $this->addFlash("success", "justificatif supprimé avec succès :)");
        }

        return $this->redirectToRoute('app_gandaal_administration_pedagogie_admin_gestion_absence_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }



    


}