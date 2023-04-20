<?php

namespace App\Controller;

use App\Repository\RecipesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipesRepository $recipesRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'recipes' => $recipesRepository->findBy([], ['createdAt' => 'desc']),
        ]);
    }
}
