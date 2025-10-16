<?php

namespace App\Service;

use App\Entity\Messages;
use App\Entity\Comment;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class Mail
{
    private MailerInterface $mailer;
    private Environment $twig;
    private UserRepository $userRepository;

    public function __construct(MailerInterface $mailer, Environment $twig, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function notifyNewMessage(Messages $message): void
    {
        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $emailAddress = $user->getEmail();
            if (!$emailAddress) {
                continue; // skip users without email
            }

            $email = (new Email())
                ->from('no-reply@example.com')
                ->to($emailAddress)
                ->subject('Nouveau message publié sur le site')
                ->html(
                    $this->twig->render('email/emails.html.twig', [
                        'message' => $message,
                        'user' => $user,
                    ])
                );

            try {
                $this->mailer->send($email);
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                error_log('Mailer failed for user ' . $user->getId() . ': ' . $e->getMessage());
            }
        }
    }

    public function notifyNewComment(Comment $comment): void
    {
        $message = $comment->getMessage();
        if (!$message) {
            return;
        }

        $author = $message->getAuthor();
        if (!$author) {
            return;
        }

        $commentAuthor = $comment->getAuthor();
        if ($commentAuthor && $author->getId() === $commentAuthor->getId()) {
            return;
        }

        $recipient = $author->getEmail();
        if (!$recipient) {
            return;
        }

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($recipient)
            ->subject('Nouveau commentaire sur votre message')
            ->text(sprintf("%s a commenté votre message :\n\n%s", $commentAuthor?->getEmail(), $comment->getContent()));

        try {
            $this->mailer->send($email);
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            error_log('notifyNewComment: mailer error when sending to ' . $recipient . ': ' . $e->getMessage());
        }
    }
}
