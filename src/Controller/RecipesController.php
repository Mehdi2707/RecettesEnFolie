<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\ConsultationUserRecipe;
use App\Entity\Favorites;
use App\Entity\Ingredients;
use App\Entity\Notes;
use App\Form\CommentsFormType;
use App\Repository\CategoriesRepository;
use App\Repository\CommentsRepository;
use App\Repository\ConsultationUserRecipeRepository;
use App\Repository\FavoritesRepository;
use App\Repository\NotesRepository;
use App\Repository\RecipesRepository;
use App\Service\GetStars;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'recipes_')]
class RecipesController extends AbstractController
{
    #[Route('/recettes', name: 'index')]
    public function index(): Response
    {
        return $this->render('recipes/index.html.twig', [
        ]);
    }

    #[Route('/recettes/{category}', name: 'category')]
    public function category($category, CategoriesRepository $categoriesRepository, GetStars $getStars): Response
    {
        $parentCategory = $categoriesRepository->findOneBy(['slug' => $category]);

        if($parentCategory == null)
        {
            $this->addFlash('warning', 'Un problème est survenue');
            return $this->redirectToRoute('recipes_index');
        }

        $categories = [];

        foreach($parentCategory->getCategories() as $category)
        {
            $categories[$category->getName()] = $category->getRecipes();
            foreach($categories[$category->getName()] as $recipe)
            {
                $notes = $recipe->getNotes();

                $recipe->noteRounded = $getStars->getStars($notes)[0];
                $recipe->hasHalfStar = $getStars->getStars($notes)[1];
            }
        }

        return $this->render('recipes/category.html.twig', [
            'parentCategory' => $parentCategory,
            'childCategories' => $categories
        ]);
    }

    #[Route('/recettes/{category}/{sCategory}', name: 'sousCategory')]
    public function sousCategory($category, $sCategory, CategoriesRepository $categoriesRepository, GetStars $getStars, RecipesRepository $recipesRepository, Request $request): Response
    {
        $parentCategory = $categoriesRepository->findOneBy(['slug' => $category]);
        $childCategory = $categoriesRepository->findOneBy(['slug' => $sCategory]);
        $page = $request->query->getInt('page', 1);

        if($parentCategory == null || $childCategory == null)
        {
            $this->addFlash('warning', 'Un problème est survenue');
            return $this->redirectToRoute('recipes_index');
        }

        $recipes = $recipesRepository->findRecipesPaginated($page, $sCategory);

        foreach($recipes['data'] as $recipe)
        {
            $notes = $recipe->getNotes();

            $recipe->noteRounded = $getStars->getStars($notes)[0];
            $recipe->hasHalfStar = $getStars->getStars($notes)[1];
        }

        return $this->render('recipes/sousCategory.html.twig', [
            'parentCategory' => $parentCategory,
            'childCategory' => $childCategory,
            'recipes' => $recipes
        ]);
    }

    #[Route('/recettes/{category}/{sCategory}/{slug}', name: 'details')]
    public function details($slug, RecipesRepository $recipesRepository, NotesRepository $notesRepository, Request $request, EntityManagerInterface $entityManager, FavoritesRepository $favoritesRepository, GetStars $getStars, ConsultationUserRecipeRepository $consultationUserRecipeRepository): Response
    {
        $recipe = $recipesRepository->findOneBy(['slug' => $slug]);
        $user = $this->getUser();
        $noteUser = $notesRepository->findOneBy(['user' => $user, 'recipe' => $recipe]);
        $notes = $recipe->getNotes();
        $favorite = $favoritesRepository->findOneBy(['user' => $user, 'recipes' => $recipe]);
        $consultedRecipes = $consultationUserRecipeRepository->findRecentlyConsultedRecipes($user, $recipe, 3);

        if($user)
        {
            $existingConsultation = $consultationUserRecipeRepository->findOneBy(['user' => $user, 'recipe' => $recipe]);

            if($existingConsultation)
            {
                $existingConsultation->setConsultedAt(new \DateTimeImmutable());

                $entityManager->persist($existingConsultation);
                $entityManager->flush();
            }
            else
            {
                $consulationUserRecipe = new ConsultationUserRecipe();
                $consulationUserRecipe->setUser($user);
                $consulationUserRecipe->setRecipe($recipe);

                $entityManager->persist($consulationUserRecipe);
                $entityManager->flush();
            }

            foreach($consultedRecipes as $consultedRecipe)
            {
                $notesRecipe = $consultedRecipe->getRecipe()->getNotes();
                $consultedRecipe->getRecipe()->noteRounded = $getStars->getStars($notesRecipe)[0];
                $consultedRecipe->getRecipe()->hasHalfStar = $getStars->getStars($notesRecipe)[1];
            }
        }

        $recipe->noteRounded = $getStars->getStars($notes)[0];
        $recipe->hasHalfStar = $getStars->getStars($notes)[1];

        $comment = new Comments();

        $form = $this->createForm(CommentsFormType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setRecipes($recipe);
            $comment->setIsActive(true);
            $comment->setUser($user);

            $parentId = $form->get('parentid')->getData();

            if($parentId != null)
                $parent = $entityManager->getRepository(Comments::class)->find($parentId);

            $comment->setParent($parent ?? null);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('recipes_details' , ['category' => $recipe->getCategories()->getParent()->getSlug(), 'sCategory' => $recipe->getCategories()->getSlug(), 'slug' => $recipe->getSlug()]);
        }

        return $this->render('recipes/details.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
            'note' => $noteUser,
            'user' => $user,
            'favorite' => $favorite,
            'consultedRecipes' => $consultedRecipes
        ]);
    }

    #[Route('/modification-commentaire', name: 'edit_comment')]
    public function editComment(Request $request, EntityManagerInterface $entityManager, CommentsRepository $commentsRepository): Response
    {
        $commentId = $request->request->get('comment_id');
        $commentContent = $request->request->get('comment_content');
        $user = $this->getUser();
        $comment = $commentsRepository->findOneBy(['id' => $commentId, 'user' => $user]);

        if (!$comment) {
            return new JsonResponse(['message' => 'Erreur interne'], 500);
        }

        // Mettez à jour le contenu du commentaire
        $comment->setContent($commentContent);
        $comment->setUpdatedAt(new \DateTimeImmutable());

        // Enregistrez les modifications dans la base de données
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
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
    }

    #[Route('/recherche/recettes/{search}', name: 'search_recipes')]
    public function searchRecipes(Request $request, RecipesRepository $recipesRepository, GetStars $getStars, $search = '')
    {
        $originalSearch = $search;
        $search = str_replace('-', ' ', $search);
        $page = $request->query->getInt('page', 1);

        if($search == "" || strlen($search) < 3)
        {
            $this->addFlash('warning', 'Veuillez entrer un mot clé de minimum trois caractères pour effectuer votre recherche');
            return $this->redirectToRoute('app_home');
        }

        $recipes = $recipesRepository->searchRecipes($page, $search);

        foreach($recipes['data'] as $recipe)
        {
            $notes = $recipe->getNotes();

            $recipe->noteRounded =  $getStars->getStars($notes)[0];
            $recipe->hasHalfStar = $getStars->getStars($notes)[1];
        }

        return $this->render('recipes/search.html.twig', [
            'search' => $search,
            'originalSearch' => $originalSearch,
            'recipes' => $recipes
        ]);
    }

    #[Route('/favoris/ajout/{slug}', name: 'favorites_add')]
    public function addFavorite($slug, RecipesRepository $recipesRepository, EntityManagerInterface $entityManager, FavoritesRepository $favoritesRepository)
    {
        $user = $this->getUser();
        $recipe = $recipesRepository->findOneBy(['slug' => $slug]);
        $existingFavorite = $favoritesRepository->findOneBy(['user' => $user, 'recipes' => $recipe]);

        if(!$user)
            return new JsonResponse(["error" => true], 400);

        if($existingFavorite)
        {
            return new JsonResponse(["error" => true], 400);
        }
        else
        {
            $favorites = new Favorites();

            $favorites->setUser($user);
            $favorites->setRecipes($recipe);

            $entityManager->persist($favorites);
            $entityManager->flush();

            return new JsonResponse(["success" => true]);
        }
    }

    #[Route('/favoris/suppression/{slug}', name: 'favorites_delete')]
    public function deleteFavorite($slug, RecipesRepository $recipesRepository, EntityManagerInterface $entityManager, FavoritesRepository $favoritesRepository)
    {
        $user = $this->getUser();
        $recipe = $recipesRepository->findOneBy(['slug' => $slug]);
        $existingFavorite = $favoritesRepository->findOneBy(['user' => $user, 'recipes' => $recipe]);

        if($existingFavorite)
        {
            $user->removeFavorite($existingFavorite);
            $recipe->removeFavorite($existingFavorite);

            $entityManager->persist($user);
            $entityManager->persist($recipe);
            $entityManager->flush();

            return new JsonResponse(["success" => true]);
        }
        else
        {
           throw new \Exception("Erreur interne");
        }
    }

    #[Route('/ingredients/recherche', name: 'ingredients_search')]
    public function ingredientsSearch(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $searchTerm = $request->query->get('search');

        // Effectuer la recherche des ingrédients dans l'entité Ingredient
        $ingredients = $entityManager->getRepository(Ingredients::class)->findBySearchTerm($searchTerm);

        $uniqueIngredients = [];
        $uniqueNames = [];

        foreach ($ingredients as $ingredient) {
            $ingredientId = $ingredient->getId();
            $ingredientName = $ingredient->getName();

            if (!in_array($ingredientName, $uniqueNames)) {
                $uniqueIngredients[] = [
                    'id' => $ingredientId,
                    'name' => $ingredientName
                ];

                $uniqueNames[] = $ingredientName;
            }
        }

        return new JsonResponse(['ingredients' => $uniqueIngredients]);
    }
}
