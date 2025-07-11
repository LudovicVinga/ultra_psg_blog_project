<?php

namespace App\Controller\Admin\Home;

use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ContactRepository;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_admin_home', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, PostRepository $postRepository, TagRepository $tagRepository, CommentRepository $commentRepository, UserRepository $userRepository, ContactRepository $contactRepository, LikeRepository $likeRepository): Response
    {
        $categories = $categoryRepository->count();
        $posts = $postRepository->count();
        $tags = $tagRepository->count();
        $comments = $commentRepository->count();
        $users = $userRepository->count();
        $contacts = $contactRepository->count();
        $likes = $likeRepository->count();

        return $this->render('pages/admin/home/index.html.twig', [
            'categories_count' => $categories,
            'posts_count' => $posts,
            'tags_count' => $tags,
            'comments_count' => $comments,
            'users_count' => $users,
            'contacts_count' => $contacts,
            'likes_count' => $likes,
        ]);
    }
}
