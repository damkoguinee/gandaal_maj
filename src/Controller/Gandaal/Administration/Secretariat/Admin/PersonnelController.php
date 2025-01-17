<?php

namespace App\Controller\Gandaal\Administration\Secretariat\Admin;

use App\Entity\Salaire;
use App\Entity\Personnel;
use App\Form\SalaireType;
use App\Form\PersonnelType;
use App\Entity\Etablissement;
use App\Entity\PersonnelActif;
use App\Entity\DocumentPersonnel;
use App\Repository\UserRepository;
use App\Form\DocumentPersonnelType;
use App\Repository\CursusRepository;
use App\Entity\HistoriqueSuppression;
use App\Entity\User;
use App\Form\UserAccesType;
use App\Repository\SalaireRepository;
use App\Repository\PersonnelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DocumentEleveRepository;
use App\Repository\ConfigFonctionRepository;
use App\Repository\PersonnelActifRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DocumentPersonnelRepository;
use App\Repository\DocumentEnseignantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/gandaal/administration/secretariat/admin')]
class PersonnelController extends AbstractController
{
    #[Route('/personnel/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_index', methods: ['GET'])]
    public function index(PersonnelActifRepository $personnelActifRep, UserRepository $userRep, Request $request, SessionInterface $session, Etablissement $etablissement): Response
    {
        if ($request->get("id_user_search")){
            $search = $request->get("id_user_search");
        }else{
            $search = "";
        }
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $personnels = $personnelActifRep->rechercheUserType1Type2ParEtablissementParPromo($search, 'personnel', 'personnel-enseignant', $etablissement, $session->get('promo'));    
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
            $personnels = $personnelActifRep->findBy(['personnel' => $search]);
        }else{
            $personnels = $personnelActifRep->listeDesPersonnelsActifParTypeParEtablissementParPromo('personnel', 'personnel-enseignant', $etablissement, $session->get('promo'));
        }
        // dd($personnels);
        return $this->render('gandaal/administration/secretariat/admin/personnel/index.html.twig', [
            'personnelsActif' => $personnels,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/personnel/new/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, SessionInterface $session, PersonnelRepository $personnelRep, Etablissement $etablissement): Response
    {
        $personnel = new Personnel();
        $form = $this->createForm(PersonnelType::class, $personnel);
        $form->handleRequest($request);

        $document = new DocumentPersonnel();
        $form_document = $this->createForm(DocumentPersonnelType::class, $document);
        $form_document->handleRequest($request);

        $salaire = new Salaire();
        $form_salaire = $this->createForm(SalaireType::class, $salaire);

        $form_salaire->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $max_id_ens = $personnelRep->findMaxId();
            $matricule = $etablissement->getInitial()."p".($max_id_ens + 1);
            $username = $personnel->getUsername();
            $username = $username ? $username : $matricule;
            $mdp=$form->get("password")->getData();
            $mdp=$mdp ? $mdp : $matricule.$personnel->getTelephone();
            $personnel->setUsername($username)
                    ->setEtablissement($etablissement)
                    ->setTypeUser('personnel')
                    ->setMatricule($personnel->getMatricule() ? $personnel->getMatricule() : $matricule)
                    ->setPassword(
                        $hasher->hashPassword(
                            $personnel,
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
                $personnel->setPhoto($nouveauNomFichier);
            }

            $signature = $form->get("signature")->getData();
            if ($signature) {
                $nomFichier= pathinfo($signature->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$signature->guessExtension();
                $signature->move($this->getParameter("dossier_personnels"),$nouveauNomFichier);
                $personnel->setSignature($nouveauNomFichier);
            }

            $entityManager->persist($personnel);

            $fichier = $form_document->get("nom")->getData();
            if ($fichier) {
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_personnels"),$nouveauNomFichier);
                $document->setNom($nouveauNomFichier);
                $personnel->addDocumentPersonnel($document);
                $entityManager->persist($document);

            }
            if ($salaire) {
                $salaireBrut = floatval(preg_replace('/[^0-9,.]/', '', $salaire->getSalaireBrut()));
                $tauxHoraire = floatval(preg_replace('/[^0-9,.]/', '', $salaire->getTauxHoraire()));
                $promo = $session->get('promo');
                $salaire->setPromo($promo)
                        ->setSalaireBrut($salaireBrut)
                        ->setTauxHoraire($tauxHoraire);
                $personnel->addSalaire($salaire);
                $entityManager->persist($salaire);
            }
            $entityManager->flush();
            $this->addFlash("success", "Personnel ajouté avec succès :)");
            return $this->redirectToRoute('app_gandaal_administration_secretariat_admin_personnel_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/secretariat/admin/personnel/new.html.twig', [
            'personnel' => $personnel,
            'form' => $form,
            'form_document' => $form_document,
            'form_salaire' => $form_salaire,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/personnel/show/{id}/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_show', methods: ['GET'])]
    public function show(Personnel $personnel, SessionInterface $session, SalaireRepository $salaireRep, DocumentPersonnelRepository $documentRep, PersonnelActifRepository $personnelActifRep, Etablissement $etablissement): Response
    {
        $salaire = $salaireRep->findOneBy(['user' => $personnel, 'promo' => $session->get('promo')]);
        $documents = $documentRep->findBy(['personnel' => $personnel]);
        $personnelActif = $personnelActifRep->findOneBy(['personnel' => $personnel]);
        // dd($personnelActif);

        return $this->render('gandaal/administration/secretariat/admin/personnel/show.html.twig', [
            'personnel' => $personnel,
            'salaire' => $salaire,
            'documents' => $documents,
            'etablissement' => $etablissement,
            'personnelActif' => $personnelActif
        ]);
    }

    #[Route('/personnel/edit/{id}/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Personnel $personnel, DocumentPersonnelRepository $documentRep, SalaireRepository $salaireRep, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, SessionInterface $session, Etablissement $etablissement): Response
    {
        $form = $this->createForm(PersonnelType::class, $personnel);
        $form->handleRequest($request);

        $document = new DocumentPersonnel();
        $form_document = $this->createForm(DocumentPersonnelType::class, $document);
        $form_document->handleRequest($request);

        $salaire = $salaireRep->findOneBy(['user' => $personnel, 'promo' => $session->get('promo')]);
        $form_salaire = $this->createForm(SalaireType::class, $salaire);
        // $form_salaire->handleRequest($request);
        // dd($salaire);

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
                    $salaire->setUser($personnel)
                        ->setSalaireBrut($salaireBrut)
                        ->setPromo($session->get('promo'));
                    $entityManager->persist($salaire);
                    $entityManager->flush();
                }                

            }

            if ($tauxHoraire) {
                $salaire = $salaireRep->findOneBy(['user' => $personnel, 'promo' => $session->get('promo')]);

                if ($salaire) {
                    $salaire->settauxHoraire($tauxHoraire);
                }else{
                    $salaire = new Salaire();
                    $salaire->setUser($personnel)
                        ->setTauxHoraire($tauxHoraire)
                        ->setPromo($session->get('promo'));
                }                
                $entityManager->persist($salaire);
            }           

            $photo =$form->get("photo")->getData();
            if ($photo) {
                if ($personnel->getPhoto()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$personnel->getPhoto();
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
                $personnel->setPhoto($nouveauNomFichier);
            }

            $signature =$form->get("signature")->getData();
            if ($signature) {
                if ($personnel->getSignature()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$personnel->getSignature();
                    if (file_exists($ancienFichier)) {
                        unlink($ancienFichier);
                    }
                }
                $nomFichier= pathinfo($signature->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$signature->guessExtension();
                $signature->move($this->getParameter("dossier_personnels"),$nouveauNomFichier);
                $personnel->setSignature($nouveauNomFichier);
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
                        ->setPersonnel($personnel);

                $entityManager->persist($document);
            }
            $mdp=$form->get("password")->getData();
            if ($mdp) {
                $mdpHashe=$hasher->hashPassword($personnel, $mdp);
                $personnel->setPassword($mdpHashe);
            }
            $entityManager->persist($personnel);
            $entityManager->flush();

            $this->addFlash("success", "Personnel modifié avec succès :)");
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('gandaal/administration/secretariat/admin/personnel/edit.html.twig', [
            'personnel' => $personnel,
            'form' => $form,
            'form_document' => $form_document,
            'form_salaire' => $form_salaire,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/personnel/confirm/delete/general/{id}/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_confirm_delete_general', methods: ['GET', 'POST'])]
    public function confirmDeleteGeneral(PersonnelActif $personnelActif, Request $request, Etablissement $etablissement): Response
    {
        $param = $request->request->get('param'); // Récupération du paramètre
        $route_suppression = $this->generateUrl('app_gandaal_administration_secretariat_admin_personnel_delete', [
            'id' => $personnelActif->getPersonnel()->getId(),
            'etablissement' => $etablissement->getId()
        ]);
        

        return $this->render('gandaal/administration/secretariat/admin/personnel/confirm_delete_general.html.twig', [
            'personnelActif' => $personnelActif,
            'etablissement' => $etablissement,
            'route_suppression' => $route_suppression,
            'param' => $param,
        ]);
    }

    #[Route('/personnel/delete/{id}/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_delete', methods: ['POST'])]
    public function delete(Request $request, Personnel $personnel, DocumentEnseignantRepository $documentRep, EntityManagerInterface $entityManager, SessionInterface $session, PersonnelActifRepository $personnelActifRep, Etablissement $etablissement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$personnel->getId(), $request->getPayload()->getString('_token'))) {
            if ($personnel->getPhoto()) {
                $ancienFichier=$this->getParameter("dossier_personnels")."/".$personnel->getPhoto();
                if (file_exists($ancienFichier)) {
                    unlink($ancienFichier);
                }
            }

            foreach ($personnel->getDocumentPersonnels() as $document) {
                if ($document->getNom()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$document->getNom();
                    if (file_exists($ancienFichier)) {
                        unlink($ancienFichier);
                    }
                }
            }

            $personnelActifs = $personnelActifRep->findBy(['personnel' => $personnel]);
            foreach ($personnelActifs as $actif) {
                $entityManager->remove($actif);

            }
            $entityManager->remove($personnel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_administration_secretariat_admin_personnel_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/personnel/new/gestion/personnel/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_new_gestion_personnel', methods: ['GET', 'POST'])]
    public function gestionPersonnel(SalaireRepository $salaireRep, SessionInterface $session, ConfigFonctionRepository $fonctionRep, UserRepository $userRep, PersonnelActifRepository $personnelActifRep, Request $request, CursusRepository $cursusRep, Etablissement $etablissement, EntityManagerInterface $em): Response
    {
        $promo = $session->get('promo');
        if ($request->get('id_personnel')) {
            $personnel = $userRep->find($request->get('id_personnel'));
            $type = $request->get('autre_fonction') ? $request->get('autre_fonction') : $personnel->getTypeUser();
            $rattachement = $request->get('rattachement');
            $rattachementPedagogieIds = $request->get('rattachementPedagogie'); // ["1", "2"]
            $rattachementPedagogie = new ArrayCollection();

            if ($rattachementPedagogieIds) {
                foreach ($rattachementPedagogieIds as $id) {
                    $cursus = $cursusRep->find($id); // Récupérer l'objet Cursus correspondant
                    if ($cursus) {
                        $rattachementPedagogie->add($cursus); // Ajouter l'objet Cursus à la collection
                    }
                }
            }

            $verifActif = $personnelActifRep->findOneBy(['personnel' => $personnel, 'promo' => $promo]);

            if ($verifActif) {
                // Effacer les rattachements pédagogiques existants avant d'ajouter les nouveaux (si nécessaire)
                foreach ($verifActif->getRattachementPedagogie() as $existingCursus) {
                    $verifActif->removeRattachementPedagogie($existingCursus); // Supprimer les anciens rattachements
                }

                // Ajouter les nouveaux rattachements pédagogiques
                foreach ($rattachementPedagogie as $cursus) {
                    $verifActif->addRattachementPedagogie($cursus);
                }

                $verifActif->setType($type)
                        ->setRattachement($rattachement);
                
                $em->persist($verifActif);
            } else {
                $personnelActif = new PersonnelActif();
                $personnelActif->setPersonnel($personnel)
                            ->setType($type)
                            ->setPromo($promo)
                            ->setRattachement($rattachement);
                
                // Ajouter les nouveaux rattachements pédagogiques
                foreach ($rattachementPedagogie as $cursus) {
                    $personnelActif->addRattachementPedagogie($cursus);
                }

                $em->persist($personnelActif);
            }

            $em->flush();
            $salaire = $salaireRep->findOneBy(['user' => $personnel, 'promo' => $promo]);
            $salaireBrut = $request->get('salaire_brut');
            $tauxHoraire = $request->get('taux_horaire');
            $salaireBrut = floatval(preg_replace('/[^0-9,.]/', '', $salaireBrut));
            $tauxHoraire = floatval(preg_replace('/[^0-9,.]/', '', $tauxHoraire));

            
            if ($salaire) {
                $salaire->setSalaireBrut($salaireBrut);
                $em->persist($salaire);
            }else{
                $salaire = new Salaire();
                $salaire->setUser($personnel)
                    ->setSalaireBrut($salaireBrut)
                    ->setPromo($session->get('promo'));
                $em->persist($salaire);
                $em->flush();
            }                

            

            // if ($tauxHoraire) {
            //     $salaire = $salaireRep->findOneBy(['user' => $personnel, 'promo' => $promo]);

            //     if ($salaire) {
            //         $salaire->settauxHoraire($tauxHoraire);
            //     }else{
            //         $salaire = new Salaire();
            //         $salaire->setUser($personnel)
            //             ->setTauxHoraire($tauxHoraire)
            //             ->setPromo($session->get('promo'));
            //     }                
            //     $em->persist($salaire);
            // }

            
            $salaire = $salaireRep->findOneBy(['user' => $personnel, 'promo' => $promo]);

            if ($salaire) {
                $salaire->settauxHoraire($tauxHoraire);
            }else{
                $salaire = new Salaire();
                $salaire->setUser($personnel)
                    ->setTauxHoraire($tauxHoraire)
                    ->setPromo($session->get('promo'));
            }                
            $em->persist($salaire);
            $em->flush();

            return new RedirectResponse($this->generateUrl('app_gandaal_administration_secretariat_admin_personnel_new_gestion_personnel', ['etablissement' => $etablissement->getId(), 'pageEncours' => $request->get('pageEncours', 1)]));  
        }

        if ($request->get("id_user_search")){
            $search = $request->get("id_user_search");
        }else{
            $search = "";
        }
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search');
            $personnels = $userRep->rechercheUserType1Type2ParEtablissement($search, 'personnel', 'enseignant', $etablissement);    
            $response = [];
            foreach ($personnels as $personnel) {
                $response[] = [
                    'nom' => ucwords($personnel->getPrenom())." ".strtoupper($personnel->getNom()),
                    'id' => $personnel->getId()
                ]; 
            }
            return new JsonResponse($response);
        }
        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("id_user_search")){
            $users = $userRep->userByIdPaginated($search);
        }else{
            $users = $userRep->listePersonnelGeneralActifParEtablissement($etablissement, $pageEncours, 20);
        }

        $personnels = [];
        foreach (($users['data'] ? $users['data'] : $users) as $value) {
            $salaire = $salaireRep->findOneBy(['user' => $value, 'promo' => $session->get('promo')]);
            $verifActif = $personnelActifRep->findOneBy(['personnel' => $value->getId(), 'promo' => $promo]);
            if ($verifActif) {
                $etat = "actif";
            }else{
                $etat = "inactif";
            }
            $personnels[] = [
                'user' => $value,
                'salaire' => $salaire,
                'etat' => $etat,
                'personnelActif' => $verifActif ? $verifActif : '',
            ];
        }
        $cursus = $cursusRep->findBy(['etablissement' => $etablissement]);
        // dd($personnels);
        return $this->render('gandaal/administration/secretariat/admin/personnel/gestion_personnel.html.twig', [
            'personnels' => $personnels,
            'etablissement' => $etablissement,
            'fonctions' => $fonctionRep->findAll(),
            'nbrePages' => $users['nbrePages'],
            'pageEncours' => $users['pageEncours'],
            'limit' => $users['limit'],
            'cursus' => $cursus
        ]);
    }

    #[Route('/personnel/confirm/delete/{id}/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_new_gestion_personnel_confirm_delete', methods: ['GET', 'POST'])]
    public function confirmDelete(PersonnelActif $personnelActif, Request $request, Etablissement $etablissement): Response
    {
        $param = $request->request->get('param'); // Récupération du paramètre
        $route_suppression = $this->generateUrl('app_gandaal_administration_secretariat_admin_personnel_new_gestion_personnel_actif_delete', [
            'id' => $personnelActif->getId(),
            'etablissement' => $etablissement->getId()
        ]);
        

        return $this->render('gandaal/administration/secretariat/admin/personnel/confirm_delete.html.twig', [
            'personnelActif' => $personnelActif,
            'etablissement' => $etablissement,
            'route_suppression' => $route_suppression,
            'param' => $param,
        ]);
    }

    #[Route('/personnel/delete/personnelActif/{id}/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_new_gestion_personnel_actif_delete', methods: ['POST'])]
    public function deletePersonnelActif(Request $request, SessionInterface $session, PersonnelActif $personnelActif, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$personnelActif->getId(), $request->getPayload()->getString('_token'))) {
            $deleteReason = $request->request->get('delete_reason');
            $information = '';
            $historique = new HistoriqueSuppression();
            $historique->setType('report personnel') // ou un type plus spécifique
                ->setMotif($deleteReason)
                ->setDateOperation(new \DateTime())
                ->setInformation($information)
                ->setOrigine('secretariat')
                ->setSaisiePar($this->getUser())
                ->setPromo($session->get('promo'))
                ->setUser($personnelActif->getPersonnel());
            $entityManager->persist($historique);
            $entityManager->remove($personnelActif);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_administration_secretariat_admin_personnel_new_gestion_personnel', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/personnel/gestion/acces/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_acces', methods: ['GET'])]
    public function gestionAcces(UserRepository $userRep, InscriptionRepository $inscriptionRep, SessionInterface $session, Request $request, Etablissement $etablissement): Response
    {
        $search = $request->get('search', null);
        $pageEnCours = $request->get('pageEncours', 1);
        
        $type = ['personnel', 'enseignant', 'eleve', 'parent', 'tuteur'];

        $users = $userRep->listeDesUtilisateursParTypeEtablissement($etablissement, $type, $pageEnCours, 100, $search);

        $filtreUsers = [];

        foreach ($users['data'] as $value) {
            $dejaExistant = false;

            // Parcourir les utilisateurs filtrés et vérifier s'il y a déjà un utilisateur avec les mêmes informations
            foreach ($filtreUsers as $filtreUser) {
                if ($filtreUser->getPrenom() === $value->getPrenom() &&
                    $filtreUser->getNom() === $value->getNom() &&
                    // $filtreUser->getTypeuser() === $value->getTypeUser() &&
                    $filtreUser->getTelephone() === $value->getTelephone()) {
                    // Si un utilisateur avec les mêmes informations existe déjà, on marque qu'il est déjà dans la liste
                    $dejaExistant = true;
                    break;
                }
            }

            // Si aucun utilisateur similaire n'existe, on ajoute l'utilisateur actuel au tableau filtré
            if (!$dejaExistant) {
                $filtreUsers[] = $value;
            }
        }
        

        return $this->render('gandaal/administration/secretariat/admin/personnel/acces.html.twig', [
            'users' => $filtreUsers,
            'etablissement' => $etablissement,
            'promo' => $session->get('promo'),
            'search' => $search,
        ]);
    }

    #[Route('/personnel/gestion/acces/edit/{id}/{etablissement}', name: 'app_gandaal_administration_secretariat_admin_personnel_acces_edit', methods: ['GET', 'POST'])]
    public function gestionAccesEdit(User $user, InscriptionRepository $inscriptionRep, SessionInterface $session, Request $request, Etablissement $etablissement, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager,): Response
    {
        $search = $request->get('search');
        $form = $this->createForm(UserAccesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {        
            $mdp=$form->get("password")->getData();
            if ($mdp) {
                $mdpHashe=$hasher->hashPassword($user, $mdp);
                $user->setPassword($mdpHashe);
            }

            
            $photo =$form->get("photo")->getData();
            if ($photo) {
                if ($user->getPhoto()) {
                    $ancienFichier=$this->getParameter("dossier_personnels")."/".$user->getPhoto();
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
                $user->setPhoto($nouveauNomFichier);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash("success", "Utilisateur modifié avec succès :)");
            return $this->redirectToRoute('app_gandaal_administration_secretariat_admin_personnel_acces', ['etablissement' => $etablissement->getId(), 'search' => $search], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/secretariat/admin/personnel/edit_acces.html.twig', [
            'user' => $user,
            'form' => $form,
            'etablissement' => $etablissement,
            'promo' => $session->get('promo'),
            'search' => $search,
        ]);
    }
}
