<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Recipes;
use App\Entity\Users;
use App\Form\ProfileFormType;
use App\Form\RecipesFormType;
use App\Repository\FavoritesRepository;
use App\Service\PictureService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $entityManager, FavoritesRepository $favoritesRepository): Response
    {
        $user = $this->getUser();
        $recipes = $entityManager->getRepository(Recipes::class)->findBy(['user' => $user]);
        $isVerified = $user->getIsVerified();
        $form = $this->createForm(ProfileFormType::class, $user, ['user' => $user]);
        $favorites = $favoritesRepository->findBy(['user' => $user]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Enregistré');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'recipes' => $recipes,
            'isVerified' => $isVerified,
            'favorites' => $favorites
        ]);
    }

    #[Route('/{user}', name: 'user')]
    public function user(EntityManagerInterface $entityManager, $user): Response
    {
        $user = $entityManager->getRepository(Users::class)->findOneBy(['username' => $user]);

        if(!$user)
        {
            $this->addFlash('warning', 'L\'utilisateur recherché n\'existe plus');
            return $this->redirectToRoute('app_home');
        }

        $recipes = $entityManager->getRepository(Recipes::class)->findBy(['user' => $user]);

        return $this->render('profile/user.html.twig', [
            'user' => $user,
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recette/ajout', name: 'recipe_add')]
    public function add(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        if(!$this->getUser()->getIsVerified())
        {
            $this->addFlash('warning', 'Vous devez activer votre compte pour publier une recette');
            return $this->redirectToRoute('profile_index');
        }
        $recipe = new Recipes();

        $form = $this->createForm(RecipesFormType::class, $recipe);

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
            $recipe->setUser($this->getUser());

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
                return $this->render('profile/recipe/add.html.twig', [
                    'recipeForm' => $form->createView(),
                ]);
            }

            if (!$hasSteps)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins une étape');
                return $this->render('profile/recipe/add.html.twig', [
                    'recipeForm' => $form->createView(),
                ]);
            }

            $entityManager->persist($recipe);
            $entityManager->flush();

            $this->addFlash('success', 'Recette publié avec succès');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/recipe/add.html.twig', [
            'recipeForm' => $form->createView()
        ]);
    }

    #[Route('/recette/edition/{slug}', name: 'recipe_edit')]
    public function edit(Recipes $recipes, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $userRecipe = $recipes->getUser();
        $user = $this->getUser();

        if($userRecipe !== $user)
            throw new Exception('Vous n\'êtes pas autorisé à accéder à cette page');

        $form = $this->createForm(RecipesFormType::class, $recipes);

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
                return $this->render('profile/recipe/edit.html.twig', [
                    'recipeForm' => $form->createView(),
                ]);
            }

            if (!$hasSteps)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins une étape');
                return $this->render('profile/recipe/edit.html.twig', [
                    'recipeForm' => $form->createView(),
                ]);
            }

            $entityManager->persist($recipes);
            $entityManager->flush();

            $this->addFlash('success', 'Recette modifié avec succès');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/recipe/edit.html.twig', [
            'recipe' => $recipes,
            'recipeForm' => $form->createView()
        ]);
    }
}
