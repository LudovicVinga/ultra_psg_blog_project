<?php

namespace App\Controller\Admin\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class CommentController extends AbstractController
{
    #[Route('/comment/index', name: 'app_admin_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findAll();

        return $this->render('pages/admin/comment/index.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/comment/{id<\d+>}', name: 'app_admin_comment_activate', methods: ['POST'])]
    public function activate(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si il y a un problème avec le jeton de sécurité, fin du script
        if (!$this->isCsrfTokenValid("activate-comment-{$comment->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_admin_comment_index');
        }

        // Si le commentaire est activé
        if ($comment->isActivated()) {
            // On le rdésactive
            $comment->setIsActivated(false);
            // On réinitialise la date de publication
            $comment->setActivatedAt(null);

            $this->addFlash('success', 'Ce commentaire a été retiré de la liste des commentaires.');
        } else { // Si le commentaire n'est pas activé
            // On l'active
            $comment->setIsActivated(true);
            $comment->setActivatedAt(new \DateTimeImmutable());

            $this->addFlash('success', 'Le commentaire a été publié.');
        }

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_comment_index');
    }

    #[Route('/comment/delete/{id<\d+>}', name: 'app_admin_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid("delete-comment-{$comment->getId()}", $request->request->get('csrf_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Le commentaire a bien été supprimé.');
        }

        return $this->redirectToRoute('app_admin_comment_index');
    }
}
