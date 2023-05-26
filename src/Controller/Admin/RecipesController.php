<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Ingredients;
use App\Entity\Recipes;
use App\Form\AdminRecipesFormType;
use App\Repository\RecipesRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECIPE_ADMIN');

        $recipe = new Recipes();

        $form = $this->createForm(AdminRecipesFormType::class, $recipe);

        $form->handleRequest($request);

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
                ]);
            }

            if (!$hasSteps)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins une étape');
                return $this->render('admin/recipes/add.html.twig', [
                    'recipeForm' => $form->createView(),
                ]);
            }

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
    public function edit(Recipes $recipes, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('RECIPE_EDIT', $recipes);

        $form = $this->createForm(AdminRecipesFormType::class, $recipes);

        $form->handleRequest($request);

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
            'recipe' => $recipes
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
