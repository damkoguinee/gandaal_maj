<?php

namespace App\Controller;

use App\Repository\EntrepriseRepository;
use App\Repository\LicenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, LicenceRepository $licenceRep, EntrepriseRepository $entrepriseRep): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

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

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
             'error' => $error,
             'entreprise' => $entrepriseRep->findOneBy([]),
             'licence' => $licence,
            'alerteExpiration' => $alerteExpiration,
            'licenceExpiree' => $licenceExpiree,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
