<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Message\SendEmailMessage;
use App\Repository\ContactRepository;
use App\Repository\FailedMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
    public function send(Contact $contact, Request $request, MessageBusInterface $messageBus, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if($request->request->count() > 0)
        {
            $message = $request->request->get('contact_form');

            $messageBus->dispatch(
                new SendEmailMessage(
                    $this->getParameter('app.mailaddress'),
                    $contact->getEmail(),
                    'Votre demande de contact - Recettes en folie',
                    'message',
                    [ 'contact' => $contact, 'message' => $message ]
                )
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

    #[Route('/emails-non-envoyes', name: 'emails_failed')]
    public function emails_failed(FailedMessageRepository $messageRepository): Response
    {
        return $this->render('admin/contact/emails_failed.html.twig', [
            'messages' => $messageRepository->findAll()
        ]);
    }

    #[Route('/emails-non-envoyes/envoi/{id}', name: 'emails_failed_resend')]
    public function emails_failed_resend(int $id, FailedMessageRepository $messageRepository, MessageBusInterface $messageBus): Response
    {
        $message = $messageRepository->find($id)->getMessage();
        $messageBus->dispatch($message);
        $messageRepository->delete($id);

        $this->addFlash('success', 'Email réenvoyé');
        return $this->redirectToRoute('admin_contact_emails_failed');
    }

    #[Route('/emails-non-envoyes/suppression/{id}', name: 'emails_failed_delete')]
    public function emails_failed_delete(int $id, FailedMessageRepository $messageRepository): Response
    {
        $messageRepository->delete($id);

        $this->addFlash('success', 'Email supprimé');
        return $this->redirectToRoute('admin_contact_emails_failed');
    }
}