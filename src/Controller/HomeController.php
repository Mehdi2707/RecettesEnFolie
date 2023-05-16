<?php

namespace App\Controller;

use App\Entity\Newsletter\UsersN;
use App\Form\NewslettersUsersNFormType;
use App\Repository\RecipesRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipesRepository $recipesRepository, Request $request, EntityManagerInterface $entityManager, SendMailService $mailService): Response
    {
        $recipes = $recipesRepository->findBy([], ['createdAt' => 'desc']);

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
                'd38.h4ck3ur@live.fr',
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
}
