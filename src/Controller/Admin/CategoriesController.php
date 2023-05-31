<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Entity\Recipes;
use App\Entity\Unit;
use App\Form\CategoriesFormType;
use App\Form\UnitsFormType;
use App\Repository\CategoriesRepository;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/categorie', name: 'admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAll();

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECIPE_ADMIN');

        $category = new Categories();

        $form = $this->createForm(CategoriesFormType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie ajouté avec succès');
            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/categories/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Categories $categories, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('CATEGORY_EDIT', $categories);

        $form = $this->createForm(CategoriesFormType::class, $categories);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($categories);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie modifié avec succès');
            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Categories $categories): Response
    {
        $this->denyAccessUnlessGranted('CATEGORY_DELETE', $categories);
        return $this->render('admin/categories/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
