<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class UserController extends AbstractController
{
    #[Route('/user/index', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('pages/admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/delete/{id<\d+>}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid("delete-user-{$user->getId()}", $request->request->get('csrf_token'))) {
            $userFullName = "{$user->getFirstName()} {$user->getLastName()}";

            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', "{$userFullName} a bien été supprimé.");
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
