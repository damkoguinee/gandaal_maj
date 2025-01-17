<?php

namespace App\Controller\Gandaal\Administration\Pedagogie\Admin;

use App\Entity\ConfigurationModule;
use App\Entity\Etablissement;
use App\Form\ConfigurationModuleType;
use App\Repository\ConfigurationModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gandaal/administration/pedagogie/admin/configuration/module')]
final class ConfigurationModuleController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_configuration_module_index', methods: ['GET'])]
    public function index(ConfigurationModuleRepository $configurationModuleRepository , Etablissement $etablissement): Response
    {
        return $this->render('gandaal/administration/pedagogie/admin/configuration_module/index.html.twig', [
            'configuration_modules' => $configurationModuleRepository->findAll(),
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/new/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_configuration_module_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager , Etablissement $etablissement): Response
    {
        $configurationModule = new ConfigurationModule();
        $form = $this->createForm(ConfigurationModuleType::class, $configurationModule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $configurationModule->setEtablissement($etablissement);
            $entityManager->persist($configurationModule);
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_administration_pedagogie_admin_configuration_module_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/pedagogie/admin/configuration_module/new.html.twig', [
            'configuration_module' => $configurationModule,
            'form' => $form,
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/show/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_configuration_module_show', methods: ['GET'])]
    public function show(ConfigurationModule $configurationModule , Etablissement $etablissement): Response
    {
        return $this->render('gandaal/administration/pedagogie/admin/configuration_module/show.html.twig', [
            'configuration_module' => $configurationModule,
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/edit/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_configuration_module_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ConfigurationModule $configurationModule, EntityManagerInterface $entityManager , Etablissement $etablissement): Response
    {
        $form = $this->createForm(ConfigurationModuleType::class, $configurationModule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gandaal_administration_pedagogie_admin_configuration_module_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gandaal/administration/pedagogie/admin/configuration_module/edit.html.twig', [
            'configuration_module' => $configurationModule,
            'form' => $form,
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('/delete/{id}/{etablissement}', name: 'app_gandaal_administration_pedagogie_admin_configuration_module_delete', methods: ['POST'])]
    public function delete(Request $request, ConfigurationModule $configurationModule, EntityManagerInterface $entityManager , Etablissement $etablissement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$configurationModule->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($configurationModule);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gandaal_administration_pedagogie_admin_configuration_module_index', ['etablissement' => $etablissement->getId()], Response::HTTP_SEE_OTHER);
    }
}
