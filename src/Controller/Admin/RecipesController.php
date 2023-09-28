<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Ingredients;
use App\Entity\Recipes;
use App\Entity\RecipeStatus;
use App\Form\AdminRecipesFormType;
use App\Form\AdminRefusedRecipeFormType;
use App\Message\SendEmailMessage;
use App\Repository\RecipesRepository;
use App\Service\PictureService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/recettes', name: 'admin_recipes_')]
class RecipesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(RecipesRepository $recipesRepository): Response
    {
        $recipes = $recipesRepository->findAll();

        return $this->render('admin/recipes/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/en-attente', name: 'waiting')]
    public function waitingRecipes(RecipesRepository $recipesRepository): Response
    {
        $recipes = $recipesRepository->findRecipesWaiting();

        return $this->render('admin/recipes/waitingRecipes.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/verification/{id}', name: 'verify')]
    public function verifyRecipe(Recipes $recipes): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_EDIT', $recipes);

        if($recipes->getRecipeStatus()->getName() !== 'en attente')
        {
            $this->addFlash('warning', 'Cette recette à déjà été traité');
            return $this->redirectToRoute('admin_recipes_waiting');
        }

        return $this->render('admin/recipes/verifyRecipe.html.twig', [
            'recipe' => $recipes,
        ]);
    }

    #[Route('/validation/{id}', name: 'validated')]
    public function validatedRecipe(Recipes $recipes, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_EDIT', $recipes);

        if($recipes->getRecipeStatus()->getName() !== 'en attente')
        {
            $this->addFlash('warning', 'Cette recette à déjà été traité');
            return $this->redirectToRoute('admin_recipes_waiting');
        }

        $status = $recipes->getRecipeStatus();
        $status->setName('valide');
        $recipes->setRecipeStatus($status);

        $entityManager->persist($recipes);
        $entityManager->flush();

        $messageBus->dispatch(
            new SendEmailMessage(
                $this->getParameter('app.mailaddress'),
                $recipes->getUser()->getEmail(),
                'Votre recette est en ligne - Recettes en folie',
                'recipeValidated',
                [ 'user' => $recipes->getUser(), 'recipe' => $recipes ]
            )
        );

        $this->addFlash('success', 'Recette traité');
        return $this->redirectToRoute('admin_recipes_waiting');
    }

    #[Route('/refus/{id}', name: 'refused')]
    public function refusedRecipe(Recipes $recipes, Request $request, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_EDIT', $recipes);

        if($recipes->getRecipeStatus()->getName() !== 'en attente')
        {
            $this->addFlash('warning', 'Cette recette à déjà été traité');
            return $this->redirectToRoute('admin_recipes_waiting');
        }

        $form = $this->createForm(AdminRefusedRecipeFormType::class, $recipes);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $status = $recipes->getRecipeStatus();
            $message = $status->getMessage();
            $status->setName('refuse');
            $recipes->setRecipeStatus($status);

            $entityManager->persist($recipes);
            $entityManager->flush();

            $messageBus->dispatch(
                new SendEmailMessage(
                    $this->getParameter('app.mailaddress'),
                    $recipes->getUser()->getEmail(),
                    'Votre recette à été refusé - Recettes en folie',
                    'recipeRefused',
                    [ 'user' => $recipes->getUser(), 'recipe' => $recipes, 'message' => $message ]
                )
            );

            $this->addFlash('success', 'Recette traité');
            return $this->redirectToRoute('admin_recipes_waiting');
        }

        return $this->render('admin/recipes/refusedRecipe.html.twig', [
            'recipe' => $recipes,
            'form' => $form->createView()
        ]);
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECIPE_ADMIN');

        $recipe = new Recipes();
        $status = new RecipeStatus();

        $form = $this->createForm(AdminRecipesFormType::class, $recipe);
        $form->handleRequest($request);
        $formSubmitted = $form->isSubmitted();

        if($form->isSubmitted() && $form->isValid())
        {
            $images = $form->get('images')->getData();

            foreach($images as $image)
            {
                $folder = 'recipes';

                $file = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($file);
                $recipe->addImage($img);
            }

            $slug = $slugger->slug($recipe->getTitle())->lower() . '-i-' . Uuid::uuid4()->toString();
            $recipe->setSlug($slug);
            $status->setName('valide');
            $recipe->setRecipeStatus($status);

            $ingredientsForm = $form->get('ingredients');
            $hasIngredients = false;
            foreach ($ingredientsForm as $ingredientForm) {
                if (!empty($ingredientForm->get('name')->getData()) && !empty($ingredientForm->get('quantity')->getData())) {
                    $hasIngredients = true;
                    break;
                }
            }

            $stepsForm = $form->get('steps');
            $hasSteps = false;
            foreach ($stepsForm as $stepForm) {
                if (!empty($stepForm->get('description')->getData())) {
                    $hasSteps = true;
                    break;
                }
            }

            if (!$hasIngredients)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins un ingrédient');
                return $this->render('admin/recipes/add.html.twig', [
                    'recipeForm' => $form->createView(),
                    'formSubmitted' => $formSubmitted
                ]);
            }

            if (!$hasSteps)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins une étape');
                return $this->render('admin/recipes/add.html.twig', [
                    'recipeForm' => $form->createView(),
                    'formSubmitted' => $formSubmitted
                ]);
            }

            $entityManager->persist($recipe);
            $entityManager->persist($status);
            $entityManager->flush();

            $this->addFlash('success', 'Recette ajouté avec succès');
            return $this->redirectToRoute('admin_recipes_index');
        }

        return $this->render('admin/recipes/add.html.twig', [
            'recipeForm' => $form->createView(),
            'formSubmitted' => $formSubmitted
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Recipes $recipes, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_EDIT', $recipes);

        $form = $this->createForm(AdminRecipesFormType::class, $recipes);
        $form->handleRequest($request);
        $formSubmitted = $form->isSubmitted();

        if($form->isSubmitted() && $form->isValid())
        {
            $images = $form->get('images')->getData();

            foreach($images as $image)
            {
                $folder = 'recipes';

                $file = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($file);
                $recipes->addImage($img);
            }

            $slug = $recipes->getSlug();
            $parts = explode("-i-", $slug);
            $id = $parts[1];
            $slug = $slugger->slug($recipes->getTitle())->lower() . '-i-' . $id;
            $recipes->setSlug($slug);

            $recipes->setUpdatedAt(new \DateTimeImmutable());

            $ingredientsForm = $form->get('ingredients');
            $hasIngredients = false;
            foreach ($ingredientsForm as $ingredientForm) {
                if (!empty($ingredientForm->get('name')->getData()) && !empty($ingredientForm->get('quantity')->getData())) {
                    $hasIngredients = true;
                    break;
                }
            }

            $stepsForm = $form->get('steps');
            $hasSteps = false;
            foreach ($stepsForm as $stepForm) {
                if (!empty($stepForm->get('description')->getData())) {
                    $hasSteps = true;
                    break;
                }
            }

            if (!$hasIngredients)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins un ingrédient');
                return $this->render('admin/recipes/edit.html.twig', [
                    'recipeForm' => $form->createView(),
                    'formSubmitted' => $formSubmitted
                ]);
            }

            if (!$hasSteps)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins une étape');
                return $this->render('admin/recipes/edit.html.twig', [
                    'recipeForm' => $form->createView(),
                ]);
            }

            $entityManager->persist($recipes);
            $entityManager->flush();

            $this->addFlash('success', 'Recette modifié avec succès');
            return $this->redirectToRoute('admin_recipes_index');
        }

        return $this->render('admin/recipes/edit.html.twig', [
            'recipeForm' => $form->createView(),
            'recipe' => $recipes,
            'formSubmitted' => $formSubmitted
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

    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $images, Request $request, EntityManagerInterface $entityManager, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $images->getId(), $data['_token']))
        {
            $name = $images->getName();

            if($pictureService->delete($name, 'recipes', 300, 300))
            {
                $entityManager->remove($images);
                $entityManager->flush();

                return new JsonResponse(['success' => true], 200);
            }
            return new JsonResponse(['error' => 'Ereur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}
