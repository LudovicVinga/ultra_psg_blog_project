<?php

namespace App\Controller\Visitor\News;

use App\Entity\Category;
use App\Entity\Tag;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(CategoryRepository $categoryRepository, TagRepository $tagRepository, PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $categories = $categoryRepository->findAll();

        $tags = $tagRepository->findAll();

        $query = $postRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC']);

        $posts = $paginator->paginate(
            $query, /* Quelles sont les données a paginer ? (La page des posts) */
            $request->query->getInt('page', 1), /* page number */
            4 /* limit per page */
        );

        return $this->render('pages/visitor/news/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'posts' => $posts,
        ]);
    }

    #[Route('/news/filtre-par-categorie/{id<\d+>}/{slug}', name: 'app_visitor_news_filter_by_category', methods: ['GET'])]
    public function postsFilterByCategory(Category $category, PostRepository $postRepository, CategoryRepository $categoryRepository, TagRepository $tagRepository, PaginatorInterface $paginator,
        Request $request): Response
    {
        $query = $postRepository->findBy(['category' => $category], ['isPublished' => 'DESC']);

        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );

        $categories = $categoryRepository->findAll();

        $tags = $tagRepository->findAll();

        return $this->render('pages/visitor/news/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    #[Route('/news/filtre-par-tag/{id<\d+>}/{slug}', name: 'app_visitor_news_filter_by_tag', methods: ['GET'])]
    public function postsFilterByTag(Tag $tag, PostRepository $postRepository, CategoryRepository $categoryRepository, TagRepository $tagRepository, PaginatorInterface $paginator,
        Request $request): Response
    {
        $query = $postRepository->filterPostByTag($tag->getId());

        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );

        $categories = $categoryRepository->findAll();

        $tags = $tagRepository->findAll();

        return $this->render('pages/visitor/news/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }
}
