<?php

namespace App\Controller\Admin;

use App\Entity\Recipes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function add(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/recipes/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Recipes $recipes): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_EDIT', $recipes);
        return $this->render('admin/recipes/index.html.twig', [
            'controller_name' => 'HomeController',
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
