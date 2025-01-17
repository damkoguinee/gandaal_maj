<?php

namespace App\Controller\Gandaal\Administration\Comptabilite;

use App\Entity\CategorieDepense;
use App\Entity\Etablissement;
use App\Form\CategorieDepenseType;
use App\Repository\CategorieDepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gandaal/administration/comptabilite/categorie/admin/depense')]
class CategorieDepenseController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_comptabilite_categorie_depense_index', methods: ['GET'])]
    public function index(CategorieDepenseRepository $categorieRep, Etablissement $etablissement): Response
    {
        return $this->render('gandaal/administration/comptabilite/categorie_depense/index.html.twig', [
            'categorie_depenses' => $categorieRep->findBy([], ['nom' => 'ASC']),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/new/{etablissement}', name: 'app_gandaal_administration_comptabilite_categorie_depense_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $categorieDepense = new CategorieDepense();
        $form = $this->createForm(CategorieDepenseType::class, $categorieDepense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieDepense);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_gandaal_administration_comptabilite_depense_new', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/comptabilite/categorie_depense/new.html.twig', [
            'categorie_depense' => $categorieDepense,
            'form' => $form,
            'etablissement' => $etablissement,
            'referer' => $request->headers->get('referer'),
        ]);
    }

    #[Route('/{id}', name: 'app_gandaal_administration_comptabilite_categorie_depense_show', methods: ['GET'])]
    public function show(CategorieDepense $categorieDepense): Response
    {
        return $this->render('gandaal/administration/comptabilite/categorie_depense/show.html.twig', [
            'categorie_depense' => $categorieDepense,
        ]);
    }

    #[Route('/{id}/{etablissement}/edit', name: 'app_gandaal_administration_comptabilite_categorie_depense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieDepense $categorieDepense, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieDepenseType::class, $categorieDepense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'modification éffectuée avec succès :)');
            return $this->redirectToRoute('app_gandaal_administration_comptabilite_categorie_depense_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/comptabilite/categorie_depense/edit.html.twig', [
            'categorie_depense' => $categorieDepense,
            'form' => $form,
            'referer' => $request->headers->get('referer'),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/{id}/{etablissement}', name: 'app_gandaal_administration_comptabilite_categorie_depense_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieDepense $categorieDepense, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorieDepense->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorieDepense);
            $entityManager->flush();
            $this->addFlash('success', 'la catégorie depense a été supprimée avec succès :)');
        }

        return $this->redirectToRoute('app_gandaal_administration_comptabilite_categorie_depense_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
