<?php

namespace App\Controller\Gandaal;

use App\Entity\Etablissement;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\CursusRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\EtablissementRepository;
use App\Repository\LicenceRepository;
use App\Repository\PersonnelActifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/gandaal/home')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_gandaal_home')]
    public function index(EntrepriseRepository $entrepriseRep, EtablissementRepository $etablissementRep, LicenceRepository $licenceRep): Response
    {
        $entreprise = $entrepriseRep->findOneBy([]);
        $etablissements = $etablissementRep->findAll();

        $licence = $licenceRep->findOneBy([]);
        // Supposons que vous avez un objet Licence avec les propriétés dateFin (DateTime)
        $dateActuelle = new \DateTime();
        $alerteExpiration = false;
        $licenceExpiree = false;

        // Vérifier si la licence est déjà expirée
        if ($licence->getTypeLicence() == 'illimité') {
            $licenceExpiree = false;
        }elseif ($licence->getDateFin() < $dateActuelle) {
            $licenceExpiree = true;
        } else {
            // Calculer la différence entre la date actuelle et la date d'expiration
            $interval = $licence->getDateFin()->diff($dateActuelle);

            // Vérifier si la licence expire dans un mois ou moins
            if ($interval->invert == 1 && $interval->days <= 30) {
                // La licence expire dans moins d'un mois
                $alerteExpiration = true;
            }
        }
        return $this->render('gandaal/home/index.html.twig', [
            'entreprise' => $entreprise,
            'etablissements' => $etablissements,
            'licence' => $licence,
            'alerteExpiration' => $alerteExpiration,
            'licenceExpiree' => $licenceExpiree,
        ]);
    }

    #[Route('/etablissement/home/{etablissement}', name: 'app_gandaal_etablissement_home')]
    public function etablissement(Etablissement $etablissement, PersonnelActifRepository $personnelActifRep, SessionInterface $session, CursusRepository $cursusRep, ConfigurationLogicielRepository $configLogicielRep, Request $request): Response
    {
        $cursus = $request->get('cursus') ? $cursusRep->find($request->get('cursus')) : Null;
        
        
        $liste_cursus = $cursusRep->findBy(['etablissement' => $etablissement]);
        $personnel = $personnelActifRep->findOneBy(['personnel' => $this->getUser(), 'promo' => $session->get('promo')]);
        $rattachements = [];
        if ($personnel) {
            foreach ($personnel->getRattachementPedagogie() as  $value) {
                $rattachements [] = $value->getNom();
            }
        }else{
            $rattachements = ['crèche', 'maternelle', 'primaire', 'collège', 'lycée', 'université'];
        }
        return $this->render('gandaal/home/accueil_etablissement.html.twig', [
            'etablissement' => $etablissement,
            'liste_cursus' => $liste_cursus,
            'cursus' => $cursus,
            'rattachements' => $rattachements,
            'personnel' => $personnel,
            'config' => $configLogicielRep->findOneBy(['etablissement' => $etablissement])
        ]);
    }

    #[Route('/administration/home/{etablissement}', name: 'app_gandaal_administration_home')]
    public function homeDirection(Etablissement $etablissement, SessionInterface $session, CursusRepository $cursusRep, Request $request): Response
    {
        $cursus = $request->get('cursus') ? $cursusRep->find($request->get('cursus')) : Null;
        $session->set('session_cursus', $cursus);
        return $this->render('gandaal/home/accueil_administration.html.twig', [
            'etablissement' => $etablissement,
            'cursus' => $cursus,
        ]);
    }
}
