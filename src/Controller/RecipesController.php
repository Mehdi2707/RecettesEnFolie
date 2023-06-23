<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\ConsultationUserRecipe;
use App\Entity\Favorites;
use App\Entity\Ingredients;
use App\Entity\Notes;
use App\Form\CommentsFormType;
use App\Form\RecipesSearchFilterFormType;
use App\Repository\CategoriesRepository;
use App\Repository\CommentsRepository;
use App\Repository\ConsultationUserRecipeRepository;
use App\Repository\DifficultyLevelRepository;
use App\Repository\FavoritesRepository;
use App\Repository\NotesRepository;
use App\Repository\RecipesRepository;
use App\Service\StarsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
    public function category($category, CategoriesRepository $categoriesRepository, RecipesRepository $recipesRepository, StarsService $starsService): Response
    {
        $parentCategory = $categoriesRepository->findOneBy(['slug' => $category]);
        $childCategory = $categoriesRepository->findChildCategories($parentCategory);

        if($parentCategory == null)
        {
            $this->addFlash('warning', 'Un problème est survenue');
            return $this->redirectToRoute('recipes_index');
        }

        $categories = [];

        foreach($childCategory as $category)
        {
            $categories[$category->getName()] = $recipesRepository->findRecipesOfsCategoryValidated($category->getSlug());
        }

        foreach($categories as $category)
        {
            $starsService->addStars($category);
        }

        return $this->render('recipes/category.html.twig', [
            'parentCategory' => $parentCategory,
            'childCategories' => $categories
        ]);
    }

    #[Route('/recettes/{category}/{sCategory}', name: 'sousCategory')]
    public function sousCategory($category, $sCategory, CategoriesRepository $categoriesRepository, StarsService $starsService, RecipesRepository $recipesRepository, Request $request): Response
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
        $starsService->addStars($recipes['data']);

        return $this->render('recipes/sousCategory.html.twig', [
            'parentCategory' => $parentCategory,
            'childCategory' => $childCategory,
            'recipes' => $recipes
        ]);
    }

    #[Route('/recettes/{category}/{sCategory}/{slug}', name: 'details')]
    public function details($category, $sCategory, $slug,
                            Request $request,
                            EntityManagerInterface $entityManager,
                            RecipesRepository $recipesRepository,
                            NotesRepository $notesRepository,
                            FavoritesRepository $favoritesRepository,
                            ConsultationUserRecipeRepository $consultationUserRecipeRepository,
                            CategoriesRepository $categoriesRepository,
                            CommentsRepository $commentsRepository,
                            StarsService $starsService): Response
    {
        // Récupérer la catégorie parente et enfant
        $parentCategory = $categoriesRepository->findOneBy(['slug' => $category]);
        $childCategory = $categoriesRepository->findOneBy(['slug' => $sCategory]);

        if (!$parentCategory || !$childCategory) {
            $this->addFlash('warning', 'Un problème est survenu');
            return $this->redirectToRoute('recipes_index');
        }

        // Récupérer la recette
        $recipe = $recipesRepository->findOneBy(['slug' => $slug]);

        if (!$recipe || $recipe->getRecipeStatus()->getName() != 'valide') {
            $this->addFlash('warning', 'Un problème est survenu');
            return $this->redirectToRoute('recipes_sousCategory', ['category' => $category, 'sCategory' => $sCategory]);
        }

        $user = $this->getUser();
        $noteUser = $notesRepository->findOneBy(['user' => $user, 'recipe' => $recipe]);
        $notes = $recipe->getNotes();
        $favorite = $favoritesRepository->findOneBy(['user' => $user, 'recipes' => $recipe]);
        $consultedRecipes = [];
        $bestRecipesOfsCategory = [];

        if ($user) {
            // Mettre à jour la date de consultation pour la recette actuelle
            $consultation = $consultationUserRecipeRepository->findOneBy(['user' => $user, 'recipe' => $recipe]);
            $consultedRecipes = $consultationUserRecipeRepository->findRecentlyConsultedRecipes($user, $recipe);

            if ($consultation) {
                $consultation->setConsultedAt(new \DateTimeImmutable());
            } else {
                $consultation = new ConsultationUserRecipe();
                $consultation->setUser($user);
                $consultation->setRecipe($recipe);
            }

            $entityManager->persist($consultation);
            $entityManager->flush();

            if(count($consultedRecipes) >= 3)
            {
                foreach ($consultedRecipes as $consultedRecipe) {
                    $notesRecipe = $consultedRecipe->getRecipe()->getNotes();
                    $consultedRecipe->getRecipe()->noteRounded = $starsService->getStars($notesRecipe)[0];
                    $consultedRecipe->getRecipe()->hasHalfStar = $starsService->getStars($notesRecipe)[1];
                }
            }
            else
            {
                $consultedRecipes = [];
                $bestRecipesOfsCategory = $recipesRepository->findBestRecipesOfsCategory($childCategory, $recipe);
                $starsService->addStars($bestRecipesOfsCategory);
            }
        }
        else
        {
            $bestRecipesOfsCategory = $recipesRepository->findBestRecipesOfsCategory($childCategory, $recipe);
            $starsService->addStars($bestRecipesOfsCategory);
        }

        $recipe->noteRounded = $starsService->getStars($notes)[0];
        $recipe->hasHalfStar = $starsService->getStars($notes)[1];

        $comments = $commentsRepository->findCommentsPaginated(1, $recipe->getSlug());

        $comment = new Comments();
        $form = $this->createForm(CommentsFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setRecipes($recipe);
            $comment->setIsActive(true);
            $comment->setUser($user);

            $parentId = $form->get('parentid')->getData();

            if ($parentId != null) {
                $parent = $entityManager->getRepository(Comments::class)->find($parentId);
                $comment->setParent($parent);
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('recipes_details' , ['category' => $recipe->getCategories()->getParent()->getSlug(), 'sCategory' => $recipe->getCategories()->getSlug(), 'slug' => $recipe->getSlug()]);
        }

        return $this->render('recipes/details.html.twig', [
            'recipe' => $recipe,
            'comments' => $comments,
            'form' => $form->createView(),
            'note' => $noteUser,
            'user' => $user,
            'favorite' => $favorite,
            'consultedRecipes' => $consultedRecipes,
            'bestRecipesOfsCategory' => $bestRecipesOfsCategory
        ]);
    }

    #[Route('/charger-commentaires/{slug}', name: 'load_comments')]
    public function loadComments($slug, Request $request, EntityManagerInterface $entityManager, CommentsRepository $commentsRepository, SerializerInterface $serializer): Response
    {
        $offset = $request->request->getInt('offset');

        $comments = $commentsRepository->findCommentsPaginated($offset, $slug, 5, true);

        return new JsonResponse($comments);
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
    public function searchRecipes(Request $request, RecipesRepository $recipesRepository, StarsService $starsService, $search = '')
    {
        $originalSearch = $search;
        $search = str_replace('-', ' ', $search);
        $page = $request->query->getInt('page', 1);

        if($search == "" || strlen($search) < 3)
        {
            $this->addFlash('warning', 'Veuillez entrer un mot clé de minimum trois caractères pour effectuer votre recherche');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(RecipesSearchFilterFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $categories = $data['categories'];
            $difficulty = $data['difficulty'];
            $prepTimeMax = $data['prepTimeMax'];
            $ingredients = $data['ingredients'];

            $recipes = $recipesRepository->searchRecipesFilter($page, $search, $categories, $difficulty, $prepTimeMax, $ingredients);
            $starsService->addStars($recipes['data']);

            return $this->render('recipes/search.html.twig', [
                'search' => $search,
                'originalSearch' => $originalSearch,
                'recipes' => $recipes,
                'form' => $form->createView()
            ]);
        }

        $recipes = $recipesRepository->searchRecipes($page, $search);
        $starsService->addStars($recipes['data']);

        return $this->render('recipes/search.html.twig', [
            'search' => $search,
            'originalSearch' => $originalSearch,
            'recipes' => $recipes,
            'form' => $form->createView()
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
