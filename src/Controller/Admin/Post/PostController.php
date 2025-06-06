<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Form\AdminPostFormType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class PostController extends AbstractController
{
    // public function __construct(
    //     private EntityManagerInterface $entityManager,
    //     private PostRepository $postRepository
    // ) {

    // }

    #[Route('/post/index', name: 'app_admin_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('pages/admin/post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create', name: 'app_admin_post_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        if (0 == count($categoryRepository->findAll())) {
            $this->addFlash('warning', 'Pour créer un nouvel article, vous devez avoir au moins une catégorie existante.');

            return $this->redirectToRoute('app_admin_category_index');
        }

        $post = new Post();

        $form = $this->createForm(AdminPostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var User
             */
            $user = $this->getUser();

            $post->setUser($user);
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', "L'article a bien été crée");

            return $this->redirectToRoute('app_admin_post_index');
        }

        return $this->render('pages/admin/post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/show/{id<\d+>}', name: 'app_admin_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('pages/admin/post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/edit/{id<\d+>}', name: 'app_admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminPostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'L\'article a bien été modifié.');

            return $this->redirectToRoute('app_admin_post_index');
        }

        return $this->render('pages/admin/post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/post/delete/{id<\d+>}', name: 'app_admin_post_delete', methods: ['POST'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid("delete-post-{$post->getId()}", $request->request->get('csrf_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();

            $this->addFlash('success', 'L\'article a bien été supprimé.');
        }

        return $this->redirectToRoute('app_admin_post_index');
    }

    #[Route('/post/publish/{id<\d+>}', name: 'app_admin_post_publish', methods: ['POST'])]
    public function publish(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si il y a un problème avec le jeton de sécurité, fin du script
        if (!$this->isCsrfTokenValid("publish-post-{$post->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_admin_post_index');
        }

        // Si l'article est déjà publié
        if ($post->isPublished()) {
            // On le retire de la liste des publication
            $post->setIsPublished(false);
            // On réinitialise la date de publication
            $post->setPublishedAt(null);

            $this->addFlash('success', 'L\'article a été retiré de la liste des publications.');
        } else { // Si l'article n'est pas publié
            // On le publie
            $post->setIsPublished(true);
            $post->setPublishedAt(new \DateTimeImmutable());

            $this->addFlash('success', 'L\'article a été publié.');
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_post_index');
    }
}
