<?php

namespace App\Controller;

use App\Entity\Newsletter\UsersN;
use App\Form\NewslettersUsersNFormType;
use App\Repository\RecipesRepository;
use App\Service\StarsService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipesRepository $recipesRepository, Request $request, EntityManagerInterface $entityManager, SendMailService $mailService, StarsService $starsService): Response
    {
        if($request->query->has('status') && $request->query->has('message'))
            $this->addFlash($request->query->get('status'), $request->query->get('message'));

        $recipes = $recipesRepository->findRecentRecipesValidated();
        $starsService->addStars($recipes);

        $userN = new UsersN();
        $form = $this->createForm(NewslettersUsersNFormType::class, $userN);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $token = hash('sha256',uniqid());
            $userN->setValidationToken($token);

            $entityManager->persist($userN);
            $entityManager->flush();

            $mailService->send(
                $this->getParameter('app.mailaddress'),
                $userN->getEmail(),
                "Inscription à la newsletter - Recettes en folie",
                "newsletter",
                [ 'user' => $userN, 'token' => $token ]
            );

            $this->addFlash('success', 'Vous êtes bien inscrit à notre newsletter, Merci !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'recipes' => $recipes,
            'form' => $form->createView()
        ]);
    }

    #[Route('/newsletter/{id}/{token}', name: 'confirm_newsletter')]
    public function confirmNewsletter(UsersN $usersN, $token, EntityManagerInterface $entityManager): Response
    {
        if($usersN->getValidationToken() != $token)
            throw $this->createNotFoundException('Page non trouvée');

        $usersN->setIsValid(true);

        $entityManager->persist($usersN);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes bien inscrit à notre newsletter');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/newsletter/desinscription/{id}/{token}', name: 'unsubscribe_newsletter')]
    public function unsubscribeNewsletter(UsersN $usersN, $token, EntityManagerInterface $entityManager): Response
    {
        if($usersN->getValidationToken() != $token)
            throw $this->createNotFoundException('Page non trouvée');

        $usersN->setIsValid(false);

        $entityManager->persist($usersN);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes bien désinscrit à notre newsletter');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/politique-de-confidentialite', name: 'rgpd')]
    public function rgpd(): Response
    {
        return $this->render('home/rgpd.html.twig', [

        ]);
    }
}
