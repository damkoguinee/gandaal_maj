<?php

namespace App\Controller\Gandaal\AdminSite;

use App\Entity\Etablissement;
use App\Entity\FonctionnementScolaire;
use App\Form\FonctionnementScolaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\FonctionnementScolaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/gandaal/admin/site/fonctionnement/scolaire')]
class FonctionnementScolaireController extends AbstractController
{
    #[Route('/index/{etablissement}', name: 'app_gandaal_admin_site_fonctionnement_scolaire_index', methods: ['GET'])]
    public function index(FonctionnementScolaireRepository $fonctionnementRep, SessionInterface $session, Etablissement $etablissement): Response
    {
        $fonctionnement = $fonctionnementRep->findBy(['etablissement' => $etablissement, 'promo' => $session->get('promo')]);
        return $this->render('gandaal/admin_site/fonctionnement_scolaire/index.html.twig', [
            'fonctionnement_scolaires' => $fonctionnement,
            'etablissement' => $etablissement
        ]);
    }

    #[Route('/new/{etablissement}', name: 'app_gandaal_admin_site_fonctionnement_scolaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Etablissement $etablissement, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $fonctionnement = new FonctionnementScolaire();
        $form = $this->createForm(FonctionnementScolaireType::class, $fonctionnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fonctionnement->setEtablissement($etablissement)
                ->setPromo($session->get('promo'));
            $entityManager->persist($fonctionnement);
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_admin_site_fonctionnement_scolaire_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/admin_site/fonctionnement_scolaire/new.html.twig', [
            'fonctionnement_scolaire' => $fonctionnement,
            'form' => $form,
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_admin_site_fonctionnement_scolaire_show', methods: ['GET'])]
    public function show(FonctionnementScolaire $fonctionnement, Etablissement $etablissement): Response
    {
        return $this->render('gandaal/admin_site/fonctionnement_scolaire/show.html.twig', [
            'fonctionnement_scolaire' => $fonctionnement,
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_admin_site_fonctionnement_scolaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SessionInterface $session, FonctionnementScolaire $fonctionnement, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FonctionnementScolaireType::class, $fonctionnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fonctionnement->setEtablissement($etablissement)
                ->setPromo($session->get('promo'));
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_admin_site_fonctionnement_scolaire_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/admin_site/fonctionnement_scolaire/edit.html.twig', [
            'fonctionnement_scolaire' => $fonctionnement,
            'form' => $form,
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/{id}/{etablissement}', name: 'app_gandaal_admin_site_fonctionnement_scolaire_delete', methods: ['POST'])]
    public function delete(Request $request, FonctionnementScolaire $fonctionnement, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fonctionnement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($fonctionnement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_admin_site_fonctionnement_scolaire_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
