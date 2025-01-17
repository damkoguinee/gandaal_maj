<?php

namespace App\Controller;

use App\Repository\ConfigurationLogicielRepository;
use App\Repository\InscriptionRepository;
use App\Repository\LicenceRepository;
use App\Repository\PersonnelActifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(SessionInterface $session, PersonnelActifRepository $personnelActifRepository, InscriptionRepository $inscriptionRep, LicenceRepository $licenceRep, ConfigurationLogicielRepository $configLogicielRep): Response
    {
        $configLogiciel = $configLogicielRep->findOneBy(['etablissement' => $this->getUser()->getEtablissement()->getId()]);

        $session->set('configLogiciel', $configLogiciel);

        $licence = $licenceRep->findOneBy([]);

        if ($this->getUser()->getTypeUser() == 'enseignant') {
            $personnel = $personnelActifRepository->findOneBy(['personnel' => $this->getUser(), 'promo' => $session->get('promo')]);
            return $this->redirectToRoute('app_gandaal_personnel', ['etablissement' => $this->getUser()->getEtablissement()->getId(), 'personnelActif' => $personnel->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($this->getUser()->getTypeUser() == 'eleve') {
            $inscription = $inscriptionRep->findOneBy(['eleve' => $this->getUser()], ['id'  => 'DESC']);
            return $this->redirectToRoute('app_gandaal_eleve', ['etablissement' => $this->getUser()->getEtablissement()->getId(), 'inscription' => $inscription->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($this->getUser()->getTypeUser() == 'parent' or $this->getUser()->getTypeUser() == 'tuteur') {
            return $this->redirectToRoute('app_gandaal_eleve_parent', [], Response::HTTP_SEE_OTHER);
        }
        
        if ($licence->getStatutSiteWeb() != 'actif') {
            return $this->redirectToRoute('app_gandaal_home', [], Response::HTTP_SEE_OTHER);
        }

       
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
