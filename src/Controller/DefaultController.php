<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{

    #[Route('/', name: 'default_home', methods: ['GET', 'POST'])]
    public function home( ): Response
    {
        //$user = $security->getUser();
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_register');
        }
        return $this->render('default/home.html.twig', [
            //'messages' => $messages,
        ]);
    }
}