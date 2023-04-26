<?php

namespace App\Controller\Admin;

use App\Entity\Recipes;
use App\Form\RecipesFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/recettes', name: 'admin_recipes_')]
class RecipesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/recipes/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $recipe = new Recipes();

        $form = $this->createForm(RecipesFormType::class, $recipe);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $slug = $slugger->slug($recipe->getTitle())->lower();
            $recipe->setSlug($slug);

            $entityManager->persist($recipe);
            $entityManager->flush();

            $this->addFlash('success', 'Recette ajouté avec succès');
            return $this->redirectToRoute('admin_recipes_index');
        }

        return $this->render('admin/recipes/add.html.twig', [
            'recipeForm' => $form->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Recipes $recipes, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_EDIT', $recipes);

        $form = $this->createForm(RecipesFormType::class, $recipes);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $slug = $slugger->slug($recipes->getTitle())->lower();
            $recipes->setSlug($slug);

            $entityManager->persist($recipes);
            $entityManager->flush();

            $this->addFlash('success', 'Recette modifié avec succès');
            return $this->redirectToRoute('admin_recipes_index');
        }

        return $this->render('admin/recipes/edit.html.twig', [
            'recipeForm' => $form->createView(),
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Recipes $recipes): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_DELETE', $recipes);
        return $this->render('admin/recipes/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
