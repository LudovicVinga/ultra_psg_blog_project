<?php

namespace App\Controller\Visitor\News;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsController extends AbstractController
{
    // public function __construct(
    //     private CategoryRepository $categoryRepository
    // )
    // {
    // }

    #[Route('/news', name: 'app_visitor_news', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository): Response
    {
        $categories = $categoryRepository->findAll();

        $tags = $tagRepository->findAll();

        $posts = $postRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC']);

        return $this->render('pages/visitor/news/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'posts' => $posts,
        ]);
    }
}
