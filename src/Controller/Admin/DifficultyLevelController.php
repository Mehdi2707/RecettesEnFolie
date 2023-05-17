<?php

namespace App\Controller\Admin;

use App\Entity\DifficultyLevel;
use App\Form\DifficultyFormType;
use App\Form\UnitsFormType;
use App\Repository\DifficultyLevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/difficulte', name: 'admin_difficulty_')]
class DifficultyLevelController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(DifficultyLevelRepository $difficultyLevelRepository): Response
    {
        $difficulty = $difficultyLevelRepository->findAll();

        return $this->render('admin/difficulty/index.html.twig', [
            'difficulty' => $difficulty,
        ]);
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECIPE_ADMIN');

        $difficulty = new DifficultyLevel();

        $form = $this->createForm(DifficultyFormType::class, $difficulty);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($difficulty);
            $entityManager->flush();

            $this->addFlash('success', 'Difficulté ajouté avec succès');
            return $this->redirectToRoute('admin_difficulty_index');
        }

        return $this->render('admin/difficulty/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(DifficultyLevel $difficultyLevel, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('DIFFICULTY_EDIT', $difficultyLevel);

        $form = $this->createForm(DifficultyFormType::class, $difficultyLevel);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($difficultyLevel);
            $entityManager->flush();

            $this->addFlash('success', 'Difficulté modifié avec succès');
            return $this->redirectToRoute('admin_difficulty_index');
        }

        return $this->render('admin/difficulty/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(DifficultyLevel $difficultyLevel): Response
    {
        $this->denyAccessUnlessGranted('DIFFICULTY_DELETE', $difficultyLevel);
        return $this->render('admin/difficulty/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
