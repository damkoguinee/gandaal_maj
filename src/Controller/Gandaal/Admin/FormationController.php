<?php

namespace App\Controller\Gandaal\Admin;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gandaal/admin/formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'app_gandaal_admin_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository): Response
    {
        return $this->render('gandaal/admin/formation/index.html.twig', [
            'formations' => $formationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_gandaal_admin_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formation);
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_admin_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/admin/formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gandaal_admin_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('gandaal/admin/formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gandaal_admin_formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_admin_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/admin/formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gandaal_admin_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($formation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_admin_formation_index', [], Response::HTTP_SEE_OTHER);
    }
}