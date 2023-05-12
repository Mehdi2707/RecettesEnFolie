<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Notes;
use App\Entity\Recipes;
use App\Form\CommentsFormType;
use App\Form\NotesFormType;
use App\Repository\NotesRepository;
use App\Repository\RecipesRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function details($slug, RecipesRepository $recipesRepository, NotesRepository $notesRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $recipes = $recipesRepository->findOneBy(['slug' => $slug]);
        $user = $this->getUser();
        $note = $notesRepository->findOneBy(['user' => $user, 'recipe' => $recipes]);

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
            'note' => $note
        ]);
    }

    #[Route('/note/{slug}', name: 'stars_request')]
    public function starsRequest($slug, Request $request, RecipesRepository $recipesRepository, NotesRepository $notesRepository, EntityManagerInterface $entityManager)
    {
        $recipe = $recipesRepository->findOneBy(['slug' => $slug]);
        $user = $this->getUser();
        $existingNote = $notesRepository->findOneBy(['user' => $user, 'recipe' => $recipe]);
        $noteSend = $request->get("note");

        if($existingNote)
        {
            $existingNote->setValue($noteSend);

            $entityManager->persist($existingNote);
            $entityManager->flush();

            return new JsonResponse("Note enregistré : " . $noteSend . " étoiles");
        }
        else
        {
            $note = new Notes();

            $note->setUser($user);
            $note->setRecipe($recipe);
            $note->setValue($noteSend);

            $entityManager->persist($note);
            $entityManager->flush();

            return new JsonResponse("Note enregistré : " . $noteSend . " étoiles");
        }

        return new JsonResponse("Une erreur est survenue");
    }
}
