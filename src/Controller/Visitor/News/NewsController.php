<?php

namespace App\Controller\Visitor\News;

use App\Entity\Tag;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\CommentFormType;
use App\Repository\TagRepository;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    #[Route('/news/article/{id<\d+>}/{slug}', name: 'app_visitor_news_post_show', methods: ['GET', 'POST'])]
    public function showPost(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setPost($post);

            /**
             * @var User
             */
            $user = $this->getUser();

            $comment->setUser($user);
            $comment->setCreatedAt(new DateTimeImmutable());
            $comment->setActivatedAt(new DateTimeImmutable());

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_visitor_news_post_show', [
                "id" => $post->getId(),
                "slug" => $post->getSlug(),
            ]);
        }

        return $this->render('pages/visitor/news/show.html.twig', [
            'post' => $post,
            "form" => $form->createView(),
        ]);
    }

    #[Route('/news/article/{id<\d+>}/{slug}/like', name: 'app_visitor_news_post_like', methods: ['GET'])]
    public function likePost(Post $post, LikeRepository $likeRepository, EntityManagerInterface $entityManager): Response
    {

        /**
         * Vérifier si il y a un utilisateur connecté
         * @var User
         */
        $user = $this->getUser();
        // Si ce n'est pas le cas
        if (null == $user) {
            // Retourner le message d'erreur correspondant
            return $this->json(['message' => 'Vous devez être connecté pour pouvoir liker!'], Response::HTTP_FORBIDDEN);
        }
        // Sinon,

        // Vérifier si l'article a deja ete liké ou non
        // Si l'article a deja été  liké
        if ($post->isLikedBy($user)) {
            // Récuperer le like,
            $like = $likeRepository->findOneBy(['post' => $post, 'user' => $user]);

            // Supprimer ce like de la table des likes
            $entityManager->remove($like);
            $entityManager->flush();

            // Retourner le message correspondant ainsi que le nombre total (a jour) de like pour cet article
            return $this->json([
                'message' => 'Le like a été retiré',
                'totalLikesUpdated' => $likeRepository->count(['post' => $post]),
            ]);
        }

        // Dans le cas contrire,
        // Creer le nouveau like
        $like = new Like();
        $like->setPost($post);
        $like->setUser($user);
        $like->setCreatedAt(new \DateTimeImmutable());
        $like->setUpdatedAt(new \DateTimeImmutable());

        // Sauvegarder le like en base de données
        $entityManager->persist($like);
        $entityManager->flush();

        // Retourner le message correspondant ainsi que le nombre total (a jour) de like pour cet article
        return $this->json([
            'message' => 'Le like a été ajouté',
            'totalLikesUpdated' => $likeRepository->count(['post' => $post]),
        ]);
    }
}
