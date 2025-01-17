<?php

namespace App\Controller\Gandaal\Administration\Comptabilite;

use App\Entity\Etablissement;
use App\Entity\CategorieRecette;
use App\Form\CategorieRecetteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieRecetteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gandaal/administration/comptabilite/categorie/admin/recette')]
class CategorieRecetteController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_comptabilite_categorie_recette_index', methods: ['GET'])]
    public function index(CategorieRecetteRepository $categorieRecetteRepository, Etablissement $etablissement): Response
    {
        return $this->render('gandaal/administration/comptabilite/categorie_recette/index.html.twig', [
            'categorie_recettes' => $categorieRecetteRepository->findBy([], ['nom' => 'ASC']),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/new/{etablissement}', name: 'app_gandaal_administration_comptabilite_categorie_recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $categorieRecette = new CategorieRecette();
        $form = $this->createForm(CategorieRecetteType::class, $categorieRecette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieRecette);
            $entityManager->flush();
            $referer = $request->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('gandaal/administration/comptabilite/categorie_recette/new.html.twig', [
            'categorie_recette' => $categorieRecette,
            'form' => $form,
            'etablissement' => $etablissement,
            'referer' => $request->headers->get('referer')
        ]);
    }

    #[Route('/{id}', name: 'app_gandaal_administration_comptabilite_categorie_recette_show', methods: ['GET'])]
    public function show(CategorieRecette $categorieRecette): Response
    {
        return $this->render('gandaal/administration/comptabilite/categorie_recette/show.html.twig', [
            'categorie_recette' => $categorieRecette,
        ]);
    }

    #[Route('/{id}/{etablissement}/edit', name: 'app_gandaal_administration_comptabilite_categorie_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieRecette $categorieRecette, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieRecetteType::class, $categorieRecette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'modification éffectuée avec succès :)');
            return $this->redirectToRoute('app_gandaal_administration_comptabilite_categorie_recette_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/comptabilite/categorie_recette/edit.html.twig', [
            'categorie_recette' => $categorieRecette,
            'form' => $form,
            'referer' => $request->headers->get('referer'),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/{id}/{etablissement}', name: 'app_gandaal_administration_comptabilite_categorie_recette_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieRecette $categorieRecette, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorieRecette->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorieRecette);
            $entityManager->flush();
            $this->addFlash('success', 'la catégorie recette a été supprimée avec succès :)');
        }

        return $this->redirectToRoute('app_gandaal_administration_comptabilite_categorie_recette_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
