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
        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $emailAddress = $user->getEmail();
            if (!$emailAddress) {
                continue;
            }

            $email = (new Email())
                ->from('no-reply@example.com')
                ->to($emailAddress)
                ->subject('Nouveau commentaire publié')
                ->html(
                    $this->twig->render('email/comment_notification.html.twig', [
                        'comment' => $comment,
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
}
