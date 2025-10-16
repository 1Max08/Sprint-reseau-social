<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{

    #[Route('/admin/users', name: 'admin_users', methods: ['GET', 'POST'])]
public function users(UserRepository $userRepository, Security $security): Response
{
    $user = $security->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    if (!in_array('ROLE_ADMIN', $user->getRoles())) {
        $this->addFlash('error', 'Accès refusé — vous devez être administrateur.');
        return $this->redirectToRoute('default_home');
    }

    $users = $userRepository->findAll();

    return $this->render('admin/users.html.twig', [
        'users' => $users,
    ]);
}

    #[Route('/admin/user/create', name: 'admin_user_create')]
public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security): Response
{
    $admin = $security->getUser();
    if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles())) {
        return $this->redirectToRoute('app_login');
    }

    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        
        $roles = $form->get('roles')->getData();
        $user->setRoles($roles);

        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/create_user.html.twig', [
            'form' => $form->createView(),
        ]);
}


#[Route('/admin/user/edit/{id}', name: 'admin_user_edit', methods: ['GET', 'POST'])]
public function editUser(Request $request, User $user, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $roles = $form->get('roles')->getData();
        $user->setRoles($roles);
        $em->flush();
        $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
        return $this->redirectToRoute('admin_users');
    }

    return $this->render('admin/edit_user.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}

#[Route('/admin/user/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
public function deleteUser(Request $request, User $user, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
    }

    return $this->redirectToRoute('admin_users');
}


}