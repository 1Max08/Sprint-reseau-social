<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Form\CreateMessageType;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessagesController extends AbstractController
{

    #[Route('/createmessage', name: 'messages_create')]
  public function create(Request $request, EntityManagerInterface $manager): Response
  {
      $message = new Messages();
      $message->setAuthor($this->getUser());

      $form = $this->createForm(CreateMessageType::class, $message);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

          $imageFile = $form->get('image')->getData();

          if ($imageFile) {
              $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
              $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
              $newFilename = $safeFilename . '-' . time() . '.' . $imageFile->guessExtension();

              $imageFile->move(
                  $this->getParameter('images_directory'),
                  $newFilename
              );

              $message->setImage('uploads/images/' . $newFilename);
          }

          $manager->persist($message);
          $manager->flush();

          return $this->redirectToRoute('default_home');
      }

      return $this->render('CRUD/createmessage.html.twig', [
          'form' => $form->createView(),
      ]);
  }

  #[Route('/message/{id}', name: 'messages_message', methods: ['GET', 'POST'])]
  public function message(
    int $id,
    MessagesRepository $messagesRepository,
    Security $security,
    Request $request,
    EntityManagerInterface $entityManager
    ): Response
    {
        if (!$security->getUser()) {
            return $this->redirectToRoute('app_register');
        }
        $message = $messagesRepository->find($id);
        if (!$message) {
            throw $this->createNotFoundException('Message introuvable');
        }
        
        if ($request->isMethod('POST')) {
            $content = trim($request->request->get('comment'));
            
            if ($content) {
                $comment = new \App\Entity\Comment();
                $comment->setContent($content);
                $comment->setAuthor($this->getUser());
                $comment->setMessage($message);
                $entityManager->persist($comment);
                $entityManager->flush();
                
                $this->addFlash('success', 'Commentaire publié avec succès.');
                return $this->redirectToRoute('messages_message', ['id' => $id]);
            }
            else {
                $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            }
        }

    return $this->render('CRUD/message_detail.html.twig', [
        'message' => $message,
    ]);
}

    #[Route('/message/update/{id}', name: 'message_update', methods: ['GET', 'POST'])]
    public function update(Request $request, Messages $message, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $message->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission de modifier ce message.');
        }

        $form = $this->createForm(CreateMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('messages_message', ['id' => $message->getId()]);
        }

        return $this->render('CRUD/message_update.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }
     #[Route('/message/delete/{id}', name: 'message_delete', methods: ['POST'])]
    public function delete(Messages $message, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $message->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce message.');
        }

        $manager->remove($message);
        $manager->flush();

        $this->addFlash('success', 'Message supprimé avec succès.');

        return $this->redirectToRoute('default_home');
    }


}