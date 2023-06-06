<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Recipes;
use App\Entity\Users;
use App\Form\ProfileFormType;
use App\Form\RecipesFormType;
use App\Repository\FavoritesRepository;
use App\Service\GetStars;
use App\Service\PictureService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'redirect_profile')]
    public function OldIndex(): Response
    {
        if($this->getUser())
            return $this->redirectToRoute('profile_index', ['user' => $this->getUser()->getUsername()]);

        return $this->redirectToRoute('app_login');
    }

    #[Route('/{user}', name: 'index')]
    public function user($user, EntityManagerInterface $entityManager, Request $request, FavoritesRepository $favoritesRepository, UserPasswordHasherInterface $passwordHasher, Security $security, GetStars $getStars): Response
    {
        $user = $entityManager->getRepository(Users::class)->findOneBy(['username' => $user]);
        $currentUser = $this->getUser();

        if($user === $currentUser && $user)
        {
            $recipes = $entityManager->getRepository(Recipes::class)->findBy(['user' => $user]);
            $isVerified = $user->getIsVerified();
            $form = $this->createForm(ProfileFormType::class, $user, ['user' => $user]);
            $favorites = $favoritesRepository->findBy(['user' => $user]);

            foreach($favorites as $recipe)
            {
                $notes = $recipe->getRecipes()->getNotes();

                $recipe->getRecipes()->noteRounded = $getStars->getStars($notes)[0];
                $recipe->getRecipes()->hasHalfStar = $getStars->getStars($notes)[1];
            }

            foreach($recipes as $recipe)
            {
                $notes = $recipe->getNotes();

                $recipe->noteRounded = $getStars->getStars($notes)[0];
                $recipe->hasHalfStar = $getStars->getStars($notes)[1];
            }

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                $user->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Enregistré');
                return $this->redirectToRoute('profile_index', ['user' => $user->getUsername()]);
            }

            if($request->request->get('confirm_delete'))
            {
                $validPassword = $passwordHasher->isPasswordValid($user, $request->request->get('confirm_delete'));

                if($validPassword)
                {
                    $user->setIsActive(false);
                    $user->setDisabledAt(new \DateTimeImmutable());

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $security->logout(false);
                    $this->addFlash('warning', 'Votre compte à bien été désactivé');
                    return $this->redirectToRoute('app_home');
                }
                else
                {
                    $this->addFlash('warning', 'Mot de passe incorrect');
                }
            }

            return $this->render('profile/index.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
                'recipes' => $recipes,
                'isVerified' => $isVerified,
                'favorites' => $favorites
            ]);
        }
        else
        {
            if(!$user)
            {
                $this->addFlash('warning', 'L\'utilisateur recherché n\'existe plus');
                return $this->redirectToRoute('app_home');
            }

            $recipes = $entityManager->getRepository(Recipes::class)->findBy(['user' => $user]);

            foreach($recipes as $recipe)
            {
                $notes = $recipe->getNotes();

                $recipe->noteRounded = $getStars->getStars($notes)[0];
                $recipe->hasHalfStar = $getStars->getStars($notes)[1];
            }

            return $this->render('profile/user.html.twig', [
                'user' => $user,
                'recipes' => $recipes,
            ]);
        }
    }

    #[Route('/recette/ajout', name: 'recipe_add')]
    public function add(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        if(!$this->getUser()->getIsVerified())
        {
            $this->addFlash('warning', 'Vous devez activer votre compte pour publier une recette');
            return $this->redirectToRoute('profile_index', ['user' => $this->getUser()->getUsername()]);
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
            return $this->redirectToRoute('profile_index', ['user' => $this->getUser()->getUsername()]);
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
            return $this->redirectToRoute('profile_index', ['user' => $user->getUsername()]);

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
            return $this->redirectToRoute('profile_index', ['user' => $user->getUsername()]);
        }

        return $this->render('profile/recipe/edit.html.twig', [
            'recipe' => $recipes,
            'recipeForm' => $form->createView()
        ]);
    }

    #[Route('/suppression/image/{id}', name: 'recipe_delete_image', methods: ['DELETE'])]
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
