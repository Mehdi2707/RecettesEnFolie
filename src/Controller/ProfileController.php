<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Recipes;
use App\Entity\Users;
use App\Form\ProfileFormType;
use App\Form\RecipesFormType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $recipes = $entityManager->getRepository(Recipes::class)->findBy(['user' => $user]);
        $isVerified = $user->getIsVerified();
        $form = $this->createForm(ProfileFormType::class, $user, ['user' => $user]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Enregistré');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'recipes' => $recipes,
            'isVerified' => $isVerified
        ]);
    }

    #[Route('/recette/edition', name: 'recipe_edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $recipeId = $request->request->get('recipe_id');
        $recipe = $entityManager->getRepository(Recipes::class)->find($recipeId);

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

            $slug = $slugger->slug($recipe->getTitle())->lower();
            $recipe->setSlug($slug);

            $entityManager->persist($recipe);
            $entityManager->flush();

            $this->addFlash('success', 'Recette modifié avec succès');
            return $this->redirectToRoute('profile_recipe_index');
        }

        return $this->render('profile/recipe/edit.html.twig', [
            'recipe' => $recipe,
            'recipeForm' => $form->createView()
        ]);
    }
}
