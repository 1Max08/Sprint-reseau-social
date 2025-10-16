<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/comment/edit/{id}', name: 'comment_edit', methods: ['GET', 'POST'])]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $manager): Response
    {
        // Only the author or admin can edit
        if (!$this->isGranted('ROLE_ADMIN') && $comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce commentaire.');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', 'Commentaire modifié avec succès.');
            return $this->redirectToRoute('messages_message', ['id' => $comment->getMessage()->getId()]);
        }

        return $this->render('CRUD/comment_update.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
        ]);
    }

    #[Route('/comment/delete/{id}', name: 'comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, EntityManagerInterface $manager): Response
    {
        // Only the author or admin can delete
        if (!$this->isGranted('ROLE_ADMIN') && $comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce commentaire.');
        }

        $messageId = $comment->getMessage()->getId();

        $manager->remove($comment);
        $manager->flush();

        $this->addFlash('success', 'Commentaire supprimé avec succès.');
        return $this->redirectToRoute('messages_message', ['id' => $messageId]);
    }
}
