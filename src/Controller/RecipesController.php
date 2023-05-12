<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Recipes;
use App\Form\CommentsFormType;
use App\Repository\RecipesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function details($slug, RecipesRepository $recipesRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $recipes = $recipesRepository->findOneBy(['slug' => $slug]);

        $user = $this->getUser();

        $comment = new Comments();

        $form = $this->createForm(CommentsFormType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setRecipes($recipes);
            $comment->setIsActive(true);
            $comment->setUser($user);

            $parentId = $form->get('parentid')->getData();

            if($parentId != null)
                $parent = $entityManager->getRepository(Comments::class)->find($parentId);

            $comment->setParent($parent ?? null);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('recipes_details' , ['slug' => $recipes->getSlug()]);
        }

        return $this->render('recipes/details.html.twig', [
            'recipe' => $recipes,
            'form' => $form->createView(),
        ]);
    }
}
