<?php

namespace App\Controller\Gandaal\AdminSite;

use App\Entity\Salaire;
use App\Form\SalaireType;
use App\Entity\Enseignant;
use App\Form\EnseignantType;
use App\Entity\Etablissement;
use App\Entity\PersonnelActif;
use App\Entity\DocumentPersonnel;
use App\Entity\DocumentEnseignant;
use App\Repository\UserRepository;
use App\Form\DocumentEnseignantType;
use App\Repository\SalaireRepository;
use App\Repository\EnseignantRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DocumentEleveRepository;
use App\Repository\PersonnelActifRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DocumentPersonnelRepository;
use App\Repository\DocumentEnseignantRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/gandaal/admin/site/enseignant')]
class EnseignantController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_admin_site_enseignant_index', methods: ['GET'])]
    public function index(PersonnelActifRepository $personnelActifRep, SessionInterface $session, UserRepository $userRep, Request $request, Etablissement $etablissement): Response
    {
        if ($request->get("id_user_search")){
            $search = $request->get("id_user_search");
        }else{
            $search = "";
        }
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $personnels = $personnelActifRep->rechercheUserType1Type2ParEtablissementParPromo($search, 'enseignant', 'personnel-enseignant', $etablissement, $session->get('promo'));  
            
            $response = [];
            foreach ($personnels as $personnelActif) {
                $response[] = [
                    'nom' => ucwords($personnelActif->getPersonnel()->getPrenom())." ".strtoupper($personnelActif->getPersonnel()->getNom()),
                    'id' => $personnelActif->getPersonnel()->getId()
                ]; 
            }
            return new JsonResponse($response);
        }
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("id_user_search")){
            $enseignants = $personnelActifRep->findBy(['personnel' => $search]);
        }else{
            $enseignants = $personnelActifRep->listeDesPersonnelsActifParTypeParEtablissementParPromo('enseignant', 'personnel-enseignant', $etablissement, $session->get('promo'));
        }
        // dd($enseignants);
        return $this->render('gandaal/admin_site/enseignant/index.html.twig', [
            'enseignants' => $enseignants,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/new/{etablissement}', name: 'app_gandaal_admin_site_enseignant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, SessionInterface $session, EnseignantRepository $enseignantRep, Etablissement $etablissement): Response
    {
        $enseignant = new Enseignant();
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);

        $document = new DocumentEnseignant();
        $form_document = $this->createForm(DocumentEnseignantType::class, $document);
        $form_document->handleRequest($request);

        $salaire = new Salaire();
        $form_salaire = $this->createForm(SalaireType::class, $salaire);

        $form_salaire->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $max_id_ens = $enseignantRep->findMaxId();
            $matricule = $etablissement->getInitial()."e".($max_id_ens + 1);
            $username = $enseignant->getUsername();
            $username = $username ? $username : $matricule;
            $mdp=$form->get("password")->getData();
            $mdp=$mdp ? $mdp : $matricule.$enseignant->getTelephone();
            $enseignant->setUsername($username)
                    ->setEtablissement($etablissement)
                    ->setTypeUser('enseignant')
                    ->setMatricule($enseignant->getMatricule() ? $enseignant->getMatricule() : $matricule)
                    ->setPassword(
                        $hasher->hashPassword(
                            $enseignant,
                            $mdp
                        )
                    );

            $fichier = $form->get("photo")->getData();
            if ($fichier) {
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_personnels"),$nouveauNomFichier);
                $enseignant->setPhoto($nouveauNomFichier);
            }

            $entityManager->persist($enseignant);

            $fichier = $form_document->get("nom")->getData();
            if ($fichier) {
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_personnels"),$nouveauNomFichier);
                $document->setNom($nouveauNomFichier);
                $enseignant->addDocumentEnseignant($document);
                $entityManager->persist($document);

            }
            if ($salaire) {
                $salaireBrut = floatval(preg_replace('/[^0-9,.]/', '', $salaire->getSalaireBrut()));
                $tauxHoraire = floatval(preg_replace('/[^0-9,.]/', '', $salaire->getTauxHoraire()));
                $promo = $session->get('promo');
                $salaire->setPromo($promo)
                        ->setSalaireBrut($salaireBrut)
                        ->setTauxHoraire($tauxHoraire);
                $enseignant->addSalaire($salaire);
                $entityManager->persist($salaire);
            }
            $entityManager->flush();
            $this->addFlash("success", "Enseignant ajouté avec succès :)");
            return $this->redirectToRoute('app_gandaal_admin_site_enseignant_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/admin_site/enseignant/new.html.twig', [
            'enseignant' => $enseignant,
            'form' => $form,
            'form_document' => $form_document,
            'form_salaire' => $form_salaire,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_admin_site_enseignant_show', methods: ['GET'])]
    public function show(Enseignant $enseignant, SessionInterface $session, SalaireRepository $salaireRep, DocumentEnseignantRepository $documentRep, PersonnelActifRepository $personnelActifRep, Etablissement $etablissement): Response
    {
        $salaire = $salaireRep->findOneBy(['user' => $enseignant, 'promo' => $session->get('promo')]);
        $documents = $documentRep->findBy(['enseignant' => $enseignant]);
        $personnelActif = $personnelActifRep->findOneBy(['personnel' => $enseignant]);

        return $this->render('gandaal/admin_site/enseignant/show.html.twig', [
            'enseignant' => $enseignant,
            'salaire' => $salaire,
            'documents' => $documents,
            'etablissement' => $etablissement,
            'personnelActif' => $personnelActif

        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_admin_site_enseignant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Enseignant $enseignant, DocumentEnseignantRepository $documentRep, SalaireRepository $salaireRep, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, SessionInterface $session, Etablissement $etablissement): Response
    {
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);

        $document = new DocumentEnseignant();
        $form_document = $this->createForm(DocumentEnseignantType::class, $document);
        $form_document->handleRequest($request);

        $salaire = $salaireRep->findOneBy(['user' => $enseignant, 'promo' => $session->get('promo')]);
        $form_salaire = $this->createForm(SalaireType::class, $salaire);
        // $form_salaire->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $salaireBrut = $request->get('salaire')['salaireBrut'];
            $tauxHoraire = $request->get('salaire')['tauxHoraire'];

            $salaireBrut = floatval(preg_replace('/[^0-9,.]/', '', $salaireBrut));
            $tauxHoraire = floatval(preg_replace('/[^0-9,.]/', '', $tauxHoraire));

            if ($salaireBrut) {
                if ($salaire) {
                    $salaire->setSalaireBrut($salaireBrut);
                    $entityManager->persist($salaire);
                }else{
                    $salaire = new Salaire();
                    $salaire->setUser($enseignant)
                        ->setSalaireBrut($salaireBrut)
                        ->setPromo($session->get('promo'));
                    $entityManager->persist($salaire);
                    $entityManager->flush();
                }                

            }

            if ($tauxHoraire) {
                $salaire = $salaireRep->findOneBy(['user' => $enseignant, 'promo' => $session->get('promo')]);

                if ($salaire) {
                    $salaire->settauxHoraire($tauxHoraire);
                }else{
                    $salaire = new Salaire();
                    $salaire->setUser($enseignant)
                        ->setTauxHoraire($tauxHoraire)
                        ->setPromo($session->get('promo'));
                }                
                $entityManager->persist($salaire);
            }
            

            $photo =$form->get("photo")->getData();
            if ($photo) {
                if ($enseignant->getPhoto()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$enseignant->getPhoto();
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
                $enseignant->setPhoto($nouveauNomFichier);
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
                        ->setEnseignant($enseignant);

                $entityManager->persist($document);
            }
            $mdp=$form->get("password")->getData();
            if ($mdp) {
                $mdpHashe=$hasher->hashPassword($enseignant, $mdp);
                $enseignant->setPassword($mdpHashe);
            }
            
            $entityManager->persist($salaire);
            $entityManager->persist($enseignant);
            $entityManager->flush();

            $this->addFlash("success", "Enseignant modifié avec succès :)");
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('gandaal/admin_site/enseignant/edit.html.twig', [
            'enseignant' => $enseignant,
            'form' => $form,
            'form_document' => $form_document,
            'form_salaire' => $form_salaire,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/confirm/delete/general/{id}/{etablissement}', name: 'app_gandaal_admin_site_enseignant_confirm_delete_general', methods: ['GET', 'POST'])]
    public function confirmDeleteGeneral(PersonnelActif $personnelActif, Request $request, Etablissement $etablissement): Response
    {
        $param = $request->request->get('param'); // Récupération du paramètre
        $route_suppression = $this->generateUrl('app_gandaal_admin_site_enseignant_delete', [
            'id' => $personnelActif->getPersonnel()->getId(),
            'etablissement' => $etablissement->getId()
        ]);
        

        return $this->render('gandaal/admin_site/personnel/confirm_delete_general.html.twig', [
            'personnelActif' => $personnelActif,
            'etablissement' => $etablissement,
            'route_suppression' => $route_suppression,
            'param' => $param,
        ]);
    }

    #[Route('/delete/{id}/{etablissement}', name: 'app_gandaal_admin_site_enseignant_delete', methods: ['POST'])]
    public function delete(Request $request, Enseignant $enseignant, DocumentEnseignantRepository $documentRep, EntityManagerInterface $entityManager, PersonnelActifRepository $personnelActifRep, Etablissement $etablissement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$enseignant->getId(), $request->getPayload()->getString('_token'))) {
            if ($enseignant->getPhoto()) {
                $ancienFichier=$this->getParameter("dossier_personnels")."/".$enseignant->getPhoto();
                if (file_exists($ancienFichier)) {
                    unlink($ancienFichier);
                }
            }

            $documents = $documentRep->findBy(['enseignant' => $enseignant]);
            foreach ($documents as $document) {
                if ($document->getNom()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$document->getNom();
                    if (file_exists($ancienFichier)) {
                        unlink($ancienFichier);
                    }
                }
            }
            $personnelActifs = $personnelActifRep->findBy(['personnel' => $enseignant]);
            foreach ($personnelActifs as $actif) {
                $entityManager->remove($actif);

            }
            $entityManager->remove($enseignant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_admin_site_enseignant_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
