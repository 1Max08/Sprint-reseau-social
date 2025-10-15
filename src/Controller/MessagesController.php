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

    #[Route('/message', name: 'messages_message', methods: ['GET', 'POST'])]
    public function message(MessagesRepository $messagesRepository, Security $security): Response
    {
       

        return $this->render('default/home.html.twig', [
           
        ]);
    }
}