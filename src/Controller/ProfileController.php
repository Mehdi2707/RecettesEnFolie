<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Recipes;
use App\Entity\RecipeStatus;
use App\Entity\Users;
use App\Form\ProfileFormType;
use App\Form\RecipesFormType;
use App\Message\SendEmailMessage;
use App\Repository\FavoritesRepository;
use App\Service\StarsService;
use App\Service\PictureService;
use App\Service\SendMailService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
    public function user($user, EntityManagerInterface $entityManager, Request $request, FavoritesRepository $favoritesRepository, UserPasswordHasherInterface $passwordHasher, Security $security, StarsService $starsService): Response
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

                $recipe->getRecipes()->noteRounded = $starsService->getStars($notes)[0];
                $recipe->getRecipes()->hasHalfStar = $starsService->getStars($notes)[1];
            }

            $starsService->addStars($recipes);

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

            $recipes = $entityManager->getRepository(Recipes::class)->findRecipesValidatedUser($user);
            $starsService->addStars($recipes);

            return $this->render('profile/user.html.twig', [
                'user' => $user,
                'recipes' => $recipes,
            ]);
        }
    }

    #[Route('/recette/ajout', name: 'recipe_add')]
    public function add(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, PictureService $pictureService, MessageBusInterface $messageBus): Response
    {
        if(!$this->getUser())
        {
            $this->addFlash('warning', 'Vous devez être connecté');
            return $this->redirectToRoute('profile_index', ['users' => $this->getUser()->getUsername()]);
        }

        if(!$this->getUser()->getIsVerified())
        {
            $this->addFlash('warning', 'Vous devez activer votre compte pour publier une recette');
            return $this->redirectToRoute('profile_index', ['user' => $this->getUser()->getUsername()]);
        }
        $recipe = new Recipes();
        $status = new RecipeStatus();

        $form = $this->createForm(RecipesFormType::class, $recipe);
        $form->handleRequest($request);
        $formSubmitted = $form->isSubmitted();

        if($form->isSubmitted() && $form->isValid())
        {
            $images = $form->get('images')->getData();

            foreach($images as $image)
            {
                $folder = 'recipes';

                $file = $pictureService->add($image, $folder, 400, 400);

                $img = new Images();
                $img->setName($file);
                $recipe->addImage($img);
            }

            $slug = $slugger->slug($recipe->getTitle())->lower() . '-i-' . Uuid::uuid4()->toString();

            $recipe->setSlug($slug);
            $recipe->setUser($this->getUser());
            $status->setName('en attente');
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
                return $this->render('profile/recipe/add.html.twig', [
                    'recipeForm' => $form->createView(),
                    'formSubmitted' => $formSubmitted
                ]);
            }

            if (!$hasSteps)
            {
                $this->addFlash('danger', 'Veuillez ajouter au moins une étape');
                return $this->render('profile/recipe/add.html.twig', [
                    'recipeForm' => $form->createView(),
                    'formSubmitted' => $formSubmitted
                ]);
            }

            $entityManager->persist($recipe);
            $entityManager->persist($status);
            $entityManager->flush();

            $messageBus->dispatch(
                new SendEmailMessage(
                    $this->getParameter('app.mailaddress'),
                    $this->getUser()->getEmail(),
                    'Suite à la publication de votre recette - Recettes en folie',
                    'recipe',
                    [ 'user' => $this->getUser(), 'recipe' => $recipe ]
                )
            );
            $this->addFlash('success', 'Votre recette à été soumis à notre équipe de modération');
            return $this->redirectToRoute('profile_index', ['user' => $this->getUser()->getUsername()]);
        }

        return $this->render('profile/recipe/add.html.twig', [
            'recipeForm' => $form->createView(),
            'formSubmitted' => $formSubmitted
        ]);
    }

    #[Route('/recette/edition/{slug}', name: 'recipe_edit')]
    public function edit(Recipes $recipes, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, PictureService $pictureService, MessageBusInterface $messageBus): Response
    {
        $userRecipe = $recipes->getUser();
        $user = $this->getUser();

        if($userRecipe !== $user)
            return $this->redirectToRoute('profile_index', ['user' => $user->getUsername()]);

        $originalTitle = $recipes->getTitle();
        $originalDescription = $recipes->getDescription();
        $originalPreparation = $recipes->getPreparationTime();
        $originalCooking = $recipes->getCookingTime();
        $originalServings = $recipes->getNumberOfServings();
        $originalCategory = $recipes->getCategories();
        $originalIngredients = $recipes->getIngredients();
        $originalSteps = $recipes->getSteps();

        $form = $this->createForm(RecipesFormType::class, $recipes);
        $form->handleRequest($request);
        $formSubmitted = $form->isSubmitted();

        if($form->isSubmitted() && $form->isValid())
        {
            $images = $form->get('images')->getData();

            foreach($images as $image)
            {
                $folder = 'recipes';

                $file = $pictureService->add($image, $folder, 400, 400);

                $img = new Images();
                $img->setName($file);
                $recipes->addImage($img);
            }

            $recipes->setUpdatedAt(new \DateTimeImmutable());

            if( $originalTitle !== $recipes->getTitle() ||
                $originalDescription !== $recipes->getDescription() ||
                $originalPreparation !== $recipes->getPreparationTime() ||
                $originalCooking !== $recipes->getCookingTime() ||
                $originalServings !== $recipes->getNumberOfServings() ||
                $originalCategory !== $recipes->getCategories() ||
                $originalIngredients !== $recipes->getIngredients() ||
                $originalSteps !== $recipes->getSteps())
            {
                $this->updateRecipeChanges($recipes, $slugger);

                if (!$this->hasIngredients($form) || !$this->hasSteps($form))
                {
                    $this->addFlash('danger', 'Veuillez ajouter au moins un ingrédient et une étape');
                    return $this->render('profile/recipe/edit.html.twig', [
                        'recipeForm' => $form->createView(),
                        'formSubmitted' => $formSubmitted
                    ]);
                }

                $entityManager->beginTransaction();
                try {
                    $entityManager->persist($recipes);
                    $entityManager->flush();
                    $entityManager->commit();
                } catch (\Exception $e) {
                    $entityManager->rollback();
                    throw $e;
                }

                $messageBus->dispatch(
                    new SendEmailMessage(
                        $this->getParameter('app.mailaddress'),
                        $this->getUser()->getEmail(),
                        'Suite à la modification de votre recette - Recettes en folie',
                        'recipe',
                        [ 'user' => $this->getUser(), 'recipe' => $recipes ]
                    )
                );

                $this->addFlash('success', 'Vos modifications ont été soumis à notre équipe de modération');
            }
            else
            {
                $entityManager->flush();
                $this->addFlash('success', 'Votre recette à bien été modifié');
            }

            return $this->redirectToRoute('profile_index', ['user' => $user->getUsername()]);
        }

        return $this->render('profile/recipe/edit.html.twig', [
            'recipe' => $recipes,
            'recipeForm' => $form->createView(),
            'formSubmitted' => $formSubmitted
        ]);
    }

    #[Route('/suppression/image/{id}', name: 'recipe_delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $images, Request $request, EntityManagerInterface $entityManager, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $images->getId(), $data['_token']))
        {
            $name = $images->getName();

            if($pictureService->delete($name, 'recipes', 400, 400))
            {
                $entityManager->remove($images);
                $entityManager->flush();

                return new JsonResponse(['success' => true], 200);
            }
            return new JsonResponse(['error' => 'Ereur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }

    private function updateRecipeChanges(Recipes $recipes, SluggerInterface $slugger)
    {
        $slug = $recipes->getSlug();
        $parts = explode("-i-", $slug);
        $id = $parts[1];
        $slug = $slugger->slug($recipes->getTitle())->lower() . '-i-' . $id;
        $recipes->setSlug($slug);

        $status = $recipes->getRecipeStatus();
        $status->setName('en attente');
        $recipes->setRecipeStatus($status);
    }

    private function hasIngredients(FormInterface $form): bool
    {
        $ingredientsForm = $form->get('ingredients');
        foreach ($ingredientsForm as $ingredientForm) {
            if (!empty($ingredientForm->get('name')->getData()) && !empty($ingredientForm->get('quantity')->getData())) {
                return true;
            }
        }
        return false;
    }

    private function hasSteps(FormInterface $form): bool
    {
        $stepsForm = $form->get('steps');
        foreach ($stepsForm as $stepForm) {
            if (!empty($stepForm->get('description')->getData())) {
                return true;
            }
        }
        return false;
    }
}
