<?php

namespace App\Controller\Gandaal\Administration\Comptabilite;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Depense;
use App\Form\DepenseType;
use App\Entity\LieuxVentes;
use App\Entity\Modification;
use App\Entity\Etablissement;
use App\Entity\MouvementCaisse;
use App\Entity\ModifDecaissement;
use App\Entity\DeleteDecaissement;
use App\Repository\DeviseRepository;
use App\Repository\DepenseRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ComptesDepotRepository;
use App\Repository\ConfigDeviseRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieDepenseRepository;
use App\Repository\ServicesGenerauxRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ModifDecaissementRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\ConfigCompteOperationRepository;
use App\Repository\ConfigCategorieOperationRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gandaal/administration/comptabilite/admin/depense')]
class DepenseController extends AbstractController
{
    #[Route('/accueil/{etablissement}', name: 'app_gandaal_administration_comptabilite_depense_index', methods: ['GET'])]
    public function index(Request $request, DepenseRepository $depenseRepository, CategorieDepenseRepository $categorieDepenseRep, SessionInterface $session, Etablissement $etablissement, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("categorie")){
            $categorie = $categorieDepenseRep->find($request->get("categorie"));
        }else{
            $categorie = "";
        }

        $firstOp = $depenseRepository->findOneBy(['promo' => $session->get('promo')], ['dateOperation' => 'ASC']);
        $date1 = $request->get("date1") ? $request->get("date1") : ($firstOp ? $firstOp->getDateOperation()->format('Y-m-d') : $request->get("date1"));
        $date2 = $request->get("date2") ? $request->get("date2") : date("Y-m-d");

        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("categorie")){
            $depense = $depenseRepository->findDepenseByLieuBySearchPaginated($etablissement, $categorie, $date1, $date2, $pageEncours, 50, $session->get('promo'));

            $cumulDepense = $depenseRepository->totalDepenseParPeriodeParLieuParCategorie($etablissement, $categorie, $date1, $date2, $session->get('promo'));
        }else{
            $depense = $depenseRepository->findDepenseByLieuPaginated($etablissement, $date1, $date2, $pageEncours, 50, $session->get('promo'));
            $cumulDepense = $depenseRepository->totalDepenseParPeriodeParLieu($etablissement, $date1, $date2, $session->get('promo'));

        }

        return $this->render('gandaal/administration/comptabilite/depense/index.html.twig', [
            'depense' => $depense,
            'entreprise' => $entrepriseRep->find(1),
            'etablissement' => $etablissement,
            'categories' => $categorieDepenseRep->findBy([], ['nom' => 'ASC']),
            'search' => $categorie,
            'cumulDepenses' => $cumulDepense,
            'categorie' => $categorie,

        ]);
    }

    #[Route('/new/{etablissement}', name: 'app_gandaal_administration_comptabilite_depense_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MouvementCaisseRepository $mouvCaisseRep, SessionInterface $session, EntityManagerInterface $entityManager, Etablissement $etablissement, ConfigCompteOperationRepository $compteOpRep, ConfigCategorieOperationRepository $categorieOpRep, DepenseRepository $depenseRep, EntrepriseRepository $entrepriseRep): Response
    {
        $depense = new Depense();
        $form = $this->createForm(DepenseType::class, $depense, ['etablissement' => $etablissement] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval($montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $caisse = $form->getViewData()->getCaisse();
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvCaisseRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant) {
                $dateDuJour = new \DateTime();
                $referenceDate = $dateDuJour->format('ymd');
                $idSuivant =($depenseRep->findCountId($etablissement, $session->get('promo')) + 1);
                $reference = "dep".$referenceDate . sprintf('%04d', $idSuivant);
                $depense->setSaisiePar($this->getUser())
                        ->setMontant(-$montant)
                        ->setTva($montant_tva)
                        ->setTaux(1)
                        ->setTypeMouvement('depense')
                        ->setEtablissement($etablissement)
                        ->setEtatOperation('clos')
                        ->setPromo($session->get('promo'))
                        ->setEtablissement($etablissement)
                        ->setReference($reference)
                        ->setDateSaisie(new \DateTime("now"))
                        ->setCategorieOperation($categorieOpRep->find(2))
                        ->setCompteOperation($compteOpRep->find(3));
                $fichier = $form->get("justificatif")->getData();
                if ($fichier) {
                    $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomFichier = $slugger->slug($nomFichier);
                    $nouveauNomFichier .="_".uniqid();
                    $nouveauNomFichier .= "." .$fichier->guessExtension();
                    $fichier->move($this->getParameter("dossier_depenses"),$nouveauNomFichier);
                    $depense->setJustificatif($nouveauNomFichier);
                }

                $entityManager->persist($depense);
                $entityManager->flush();
                $this->addFlash("success", "dépense ajoutée avec succès :)");
                return $this->redirectToRoute('app_gandaal_administration_comptabilite_depense_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('gandaal/administration/comptabilite/depense/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'etablissement' => $etablissement,
                        'form' => $formView,
                        'depense' => $depense,
                        'referer' => $referer,
                    ]);
                }
            }
            
        }

        return $this->render('gandaal/administration/comptabilite/depense/new.html.twig', [
            'depense' => $depense,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_administration_comptabilite_depense_show', methods: ['GET'])]
    public function show(Depense $depense, Etablissement $etablissement, EntrepriseRepository $entrepriseRep): Response
    {
        // $depense_modif = $modifDecRep->findBy(['depense' => $depense]);
        return $this->render('gandaal/administration/comptabilite/depense/show.html.twig', [
            'depense' => $depense,
            'entreprise' => $entrepriseRep->find(1),
            'etablissement' => $etablissement,
            'depense_modif' =>  [],

        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_administration_comptabilite_depense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Depense $depense, MouvementCaisseRepository $mouvCaisseRep, DepenseRepository $depenseRep, EntityManagerInterface $entityManager, Etablissement $etablissement, EntrepriseRepository $entrepriseRep): Response
    {
        $depense_init = $depenseRep->find($depense);
        // $depense_modif = new Modification();
        // $depense_modif->setMontant($depense_init->getMontant())
        //                 ->setCaisse($depense_init->getCaisse())
        //                 ->setOrigine("depense")
        //                 ->setDevise($depense_init->getDevise())
        //                 ->setSaisiePar($depense_init->getTraitePar())
        //                 ->setDateOperation($depense_init->getDateDepense())
        //                 ->setDepense($depense_init);
        // $entityManager->persist($depense_modif);

        $form = $this->createForm(DepenseType::class, $depense, ['etablissement' => $etablissement] );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $montantString = $form->get('montant')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant = floatval(- $montantString);

            $montantString = $form->get('tva')->getData();
            $montantString = preg_replace('/[^0-9,.]/', '', $montantString);
            $montant_tva = floatval($montantString);

            $caisse = $form->getViewData()->getCaisse();
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvCaisseRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant) {
                $depense->setMontant($montant)
                        ->setTva($montant_tva)
                        ->setSaisiePar($this->getUser())
                        ->setDateSaisie(new \DateTime("now"));

                $justificatif =$form->get("justificatif")->getData();
                if ($justificatif) {
                    if ($depense->getJustificatif()) {
                        $ancienJustificatif=$this->getParameter("dossier_depenses")."/".$depense->getJustificatif();
                        if (file_exists($ancienJustificatif)) {
                            unlink($ancienJustificatif);
                        }
                    }
                    $nomJustificatif= pathinfo($justificatif->getClientOriginalName(), PATHINFO_FILENAME);
                    $slugger = new AsciiSlugger();
                    $nouveauNomJustificatif = $slugger->slug($nomJustificatif);
                    $nouveauNomJustificatif .="_".uniqid();
                    $nouveauNomJustificatif .= "." .$justificatif->guessExtension();
                    $justificatif->move($this->getParameter("dossier_depense"),$nouveauNomJustificatif);
                    $depense->setJustificatif($nouveauNomJustificatif);

                }

                $entityManager->persist($depense);
                $entityManager->flush();
                $this->addFlash("success", "Dépense enregistrée avec succès :)");
                return $this->redirectToRoute('app_gandaal_administration_comptabilite_depense_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    return $this->render('gandaal/administration/comptabilite/depense/edit.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'etablissement' => $etablissement,
                        'form' => $formView,
                        'depense' => $depense,
                        'referer' => $referer,
                    ]);
                }
            }

        }

        return $this->render('gandaal/administration/comptabilite/depense/edit.html.twig', [
            'depense' => $depense,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/delete/{id}/{etablissement}', name: 'app_gandaal_administration_comptabilite_depense_delete', methods: ['POST'])]
    public function delete(Request $request, Depense $depense, Filesystem $filesystem, EntityManagerInterface $entityManager, Etablissement $etablissement, EntrepriseRepository $entrepriseRep): Response
    {
        $justificatif = $depense->getJustificatif();
        if ($this->isCsrfTokenValid('delete'.$depense->getId(), $request->request->get('_token'))) {
            $pdfPath = $this->getParameter("dossier_depenses") . '/' . $justificatif;
            // Si le chemin du justificatif existe, supprimez également le fichier
            if ($justificatif && $filesystem->exists($pdfPath)) {
                $filesystem->remove($pdfPath);
            }

            // $delete_dec = new DeleteDecaissement();
            // $delete_dec->setMontant($depense->getMontant())
            //         ->setDateSaisie(new \DateTime("now"))
            //         ->setClient("depense")
            //         ->setSaisiePar($depense->getTraitePar()->getPrenom().' '.$depense->getTraitePar()->getNom())
            //         ->setDevise($depense->getDevise()->getNomDevise())
            //         ->setCaisse($depense->getCaisse()->getDesignation())
            //         ->setDateDecaissement($depense->getDateDepense())
            //         ->setCommentaire($depense->getDescription());

            // $entityManager->persist($delete_dec);
            $entityManager->remove($depense);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_administration_comptabilite_depense_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/depense/pdf/{etablissement}', name: 'app_gandaal_administration_comptabilite_depense_pdf')]
    public function salairePersonnel(Etablissement $etablissement, SessionInterface $session, DepenseRepository $depenseRepository, CategorieDepenseRepository $categorieDepenseRep, Request $request ): Response
    {       
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/config/'.$etablissement->getEntreprise()->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $symbolePath = $this->getParameter('kernel.project_dir') . '/public/images/config/symbole.png';
        $symboleBase64 = base64_encode(file_get_contents($symbolePath));
        $ministerePath = $this->getParameter('kernel.project_dir') . '/public/images/config/ministere.jpg';
        $ministereBase64 = base64_encode(file_get_contents($ministerePath));

        if ($request->get("categorie")){
            $categorie = $categorieDepenseRep->find($request->get("categorie"));
        }else{
            $categorie = "";
        }

        $firstOp = $depenseRepository->findOneBy(['promo' => $session->get('promo')], ['dateOperation' => 'ASC']);
        $date1 = $request->get("date1") ? $request->get("date1") : ($firstOp ? $firstOp->getDateOperation()->format('Y-m-d') : $request->get("date1"));
        $date2 = $request->get("date2") ? $request->get("date2") : date("Y-m-d");

        $pageEncours = $request->get('pageEncours', 1);
        if ($request->get("categorie")){
            $depense = $depenseRepository->findDepenseByLieuBySearchPaginated($etablissement, $categorie, $date1, $date2, $pageEncours, 2500, $session->get('promo'));

            $cumulDepense = $depenseRepository->totalDepenseParPeriodeParLieuParCategorie($etablissement, $categorie, $date1, $date2, $session->get('promo'));
        }else{
            $depense = $depenseRepository->findDepenseByLieuPaginated($etablissement, $date1, $date2, $pageEncours, 2500, $session->get('promo'));
            $cumulDepense = $depenseRepository->totalDepenseParPeriodeParLieu($etablissement, $date1, $date2, $session->get('promo'));

        }  
        
        // Grouper les dépenses par catégorie
        $depensesGroupeesParCategorie = [];
        foreach ($depense['data'] as $dep) {
            $categorieDepense = $dep->getCategorieDepense()->getNom(); // Assume que getNom() retourne le nom de la catégorie
            if (!isset($depensesGroupeesParCategorie[$categorieDepense])) {
                $depensesGroupeesParCategorie[$categorieDepense] = [];
            }
            $depensesGroupeesParCategorie[$categorieDepense][] = $dep;
        }
        
        
        $html = $this->renderView('gandaal/administration/comptabilite/depense/depense_pdf.html.twig', [           
            'logoPath' => $logoBase64,
            'symbolePath' => $symboleBase64,
            'ministerePath' => $ministereBase64,
            'etablissement' => $etablissement,
            'depensesGroupeesParCategorie' => $depensesGroupeesParCategorie,
            'cumulDepenses' => $cumulDepense,
            'date1' => $date1,
            'date2' => $date2,
            'categorie' => $categorie,
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
            'Content-Disposition' => 'inline; filename=depenses_'.date("d/m/Y à H:i").'".pdf"',
        ]);
    }
}
