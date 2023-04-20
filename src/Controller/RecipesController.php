<?php

namespace App\Controller;

use App\Entity\Recipes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recettes', name: 'recipes_')]
class RecipesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('recipes/index.html.twig', [
            'controller_name' => 'RecipesController',
        ]);
    }

    #[Route('/{slug}', name: 'details')]
    public function details(Recipes $recipes): Response
    {
        $ingredients = $recipes->getIngredients();
        $steps = $recipes->getSteps();

        return $this->render('recipes/details.html.twig', [
            'ingredients' => $ingredients,
            'recipe' => $recipes,
            'steps' => $steps
        ]);
    }
}
