<?php

namespace App\Controller\Gandaal\Admin;

use App\Entity\CategorieMatiere;
use App\Form\CategorieMatiereType;
use App\Repository\CategorieMatiereRepository;
use App\Repository\EleveRepository;
use App\Repository\FraisScolariteRepository;
use App\Repository\InscriptionRepository;
use App\Repository\PaiementEleveRepository;
use App\Repository\TranchePaiementRepository;
use App\Repository\UserMajRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gandaal/admin/reajustement')]
class ReajustementController extends AbstractController
{
    #[Route('/photo', name: 'app_gandaal_admin_reajustement_photo', methods: ['GET'])]
    public function index(EleveRepository $eleveRep, Request $request, EntityManagerInterface $em, InscriptionRepository $inscriptionRep): Response
    {
        $eleves = $eleveRep->findAll();

        foreach ($eleves as $key => $eleve) {
            // Récupère la photo actuelle
            $photo = $eleve->getPhoto();
            if ($photo) {
                # code...
                // Utilise une expression régulière pour vérifier si la photo a déjà une extension
                if (!preg_match('/\.(jpg|jpeg|png|gif|bmp|tiff|webp)$/i', $photo)) {
                    // Si aucune extension d'image n'est trouvée, ajoute '.jpg' par défaut
                    $eleve->setPhoto($photo . '.jpg');
                    
                    // Persist la modification
                    $em->persist($eleve);
                }
            }else{
                $eleve->setPhoto("default.jpg");
                $em->persist($eleve);
            }
            
        }
        $em->flush();
        $referer = $request->headers->get('referer');
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
   
    }

    #[Route('/naissance', name: 'app_gandaal_admin_reajustement_naissance', methods: ['GET'])]
    public function naissance(EleveRepository $eleveRep, UserMajRepository $userMajRep, UserRepository $userRep, Request $request, EntityManagerInterface $em, InscriptionRepository $inscriptionRep): Response
    {
        $eleves = $userMajRep->findBy(['typeUser' => 'eleve']);

        foreach ($eleves as $key => $eleve) {
            $user = $userRep->find($eleve);
            
            if ($eleve->getDateNaissance()) {
                if (!$user->getDateNaissance()) {
                    $user->setDateNaissance($eleve->getDateNaissance());

                }
            }

            
        }
        $em->flush();
        $referer = $request->headers->get('referer');
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
   
    }

    #[Route('/tranche', name: 'app_gandaal_admin_reajustement_tranche', methods: ['GET'])]
    public function scolarite(FraisScolariteRepository $fraisScolariteRepository, TranchePaiementRepository $trancheRep, SessionInterface $session, Request $request, EntityManagerInterface $em): Response
    {
        // $frais = $fraisScolariteRepository->findBy(['promo' => $session->get('promo')]);
        // foreach ($frais as $key => $value) {
        //     $value->setTranche(($value->getTranche()->getId() == 1) ? $trancheRep->find(4) : $trancheRep->find(5));
        //     $em->persist($value);
        //     // dd($value);
        // }
        $em->flush();
        $referer = $request->headers->get('referer');
        // return $this->redirect($referer);  
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);

    }
}
