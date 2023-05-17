<?php

namespace App\Controller\Admin;

use App\Entity\Newsletter\Newsletters;
use App\Entity\Newsletter\UsersN;
use App\Form\NewslettersFormType;
use App\Repository\Newsletter\NewslettersRepository;
use App\Repository\Newsletter\UsersNRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [

        ]);
    }

    #[Route('/newsletter', name: 'newsletter')]
    public function newsletter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $newsletter = new Newsletters();
        $form = $this->createForm(NewslettersFormType::class, $newsletter);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($newsletter);
            $entityManager->flush();

            return $this->redirectToRoute('admin_newsletter_list');
        }

        return $this->render('admin/newsletter/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/newsletter/liste', name: 'newsletter_list')]
    public function newsletterList(NewslettersRepository $newslettersRepository, UsersNRepository $usersNRepository): Response
    {
        $usersInscrit = count($usersNRepository->findBy(['is_valid' => true]));

        return $this->render('admin/newsletter/list.html.twig', [
            'newsletters' => $newslettersRepository->findAll(),
            'usersInscrit' => $usersInscrit
        ]);
    }

    #[Route('/newsletter/envoi/{id}', name: 'newsletter_send')]
    public function newsletterSend(UsersNRepository $usersNRepository, SendMailService $mailService, Newsletters $newsletters, EntityManagerInterface $entityManager): Response
    {
        $users = $usersNRepository->findBy(['is_valid' => true]);

        foreach($users as $user)
        {
            $mailService->send(
                'd38.h4ck3ur@live.fr',
                $user->getEmail(),
                $newsletters->getName(),
                'newsletterSend',
                [ 'newsletter' => $newsletters, 'user' => $user ]
            );
        }

        $newsletters->setIsSent(true);
        $entityManager->persist($newsletters);
        $entityManager->flush();

        $this->addFlash('success', 'Newsletter envoyé !');
        return $this->redirectToRoute('admin_newsletter_list');
    }
}