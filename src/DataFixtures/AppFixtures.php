<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Messages;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des admins
        $admins = [];
        for ($i = 1; $i <= 2; $i++) {
            $admin = new User();
            $admin->setEmail("admin$i@admin.fr");
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $manager->persist($admin);
            $admins[] = $admin;
        }

        // Création des utilisateurs standards
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("user$i@user.fr");
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
            $manager->persist($user);
            $users[] = $user;
        }

        // Liste complète des utilisateurs (admin + user)
        $allUsers = array_merge($admins, $users);

        // Création des messages avec auteurs utilisateurs standards
        // ...
for ($i = 1; $i <= 5; $i++) {
    $author = $users[array_rand($users)];
    $message = new Messages();
    $message->setAuthor($author);
    $message->setContent("Message numéro $i de l’utilisateur {$author->getEmail()}");
    $message->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 10) . ' days'));

    // Ajout de l'image par défaut
    $message->setImage('uploads/images/image-default.jpg'); // Mets ici le nom ou chemin de ton image par défaut

    $manager->persist($message);

    // Commentaires...


            // 3 commentaires par message, auteurs aléatoires parmi tous les users
            for ($j = 1; $j <= 3; $j++) {
                $commentAuthor = $allUsers[array_rand($allUsers)];
                $comment = new Comment();
                $comment->setMessage($message);
                $comment->setAuthor($commentAuthor);
                $comment->setContent("Commentaire $j sur le message $i par {$commentAuthor->getEmail()}");
                $comment->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 5) . ' days'));
                $manager->persist($comment);
            }
        }

        // Création des messages avec auteurs admins
        for ($i = 1; $i <= 3; $i++) {
    $author = $admins[array_rand($admins)];
    $message = new Messages();
    $message->setAuthor($author);
    $message->setContent("Post de l’admin {$author->getEmail()} numéro $i");
    $message->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 10) . ' days'));

    // Image par défaut
    $message->setImage('uploads/images/image-default.jpg');

    $manager->persist($message);

    // Commentaire...



            // 1 commentaire par message, auteur aléatoire parmi tous les users
            $commentAuthor = $allUsers[array_rand($allUsers)];
            $comment = new Comment();
            $comment->setMessage($message);
            $comment->setAuthor($commentAuthor);
            $comment->setContent("Réponse de {$commentAuthor->getEmail()} sur le post admin numéro $i");
            $comment->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 5) . ' days'));
            $manager->persist($comment);
        }

        $manager->flush();
    }
}
