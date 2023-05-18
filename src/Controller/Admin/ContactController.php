<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/contact', name: 'admin_contact_')]
class ContactController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findBy(['is_done' => false]);

        return $this->render('admin/contact/index.html.twig', [
            'contacts' => $contacts
        ]);
    }

    #[Route('/details/{id}', name: 'details')]
    public function details(Contact $contact): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/contact/details.html.twig', [
            'contact' => $contact
        ]);
    }

    #[Route('/répondre/{id}', name: 'send')]
    public function send(Contact $contact, Request $request, SendMailService $mailService, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if($request->request->count() > 0)
        {
            $message = $request->request->get('contact_form');

            $mailService->send(
                'd38.h4ck3ur@live.fr',
                $contact->getEmail(),
                'Votre demande de contact - Recettes en folie',
                'message',
                [ 'contact' => $contact, 'message' => $message ]
            );

            $contact->setIsDone(true);
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réponse à bien été envoyer, demande close');
            return $this->redirectToRoute('admin_contact_index');
        }

        return $this->render('admin/contact/send.html.twig', [
            'contact' => $contact
        ]);
    }

    #[Route('/terminer', name: 'done')]
    public function done(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findBy(['is_done' => true]);

        return $this->render('admin/contact/index.html.twig', [
            'contacts' => $contacts
        ]);
    }
}