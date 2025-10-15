<?php

namespace App\Controller;

use App\Repository\MessagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessagesController extends AbstractController
{

    #[Route('/createmessage', name: 'messages_create', methods: ['GET', 'POST'])]
    public function create(MessagesRepository $messagesRepository, Security $security): Response
    {
       

        return $this->render('default/home.html.twig', [
           
        ]);
    }

    #[Route('/message', name: 'messages_message', methods: ['GET', 'POST'])]
    public function message(MessagesRepository $messagesRepository, Security $security): Response
    {
       

        return $this->render('default/home.html.twig', [
           
        ]);
    }
}