<?php

namespace App\Controller\Visitor\Welcome;

use App\Repository\PostRepository;
use App\Services\ApiFootballService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    #[Route('/', name: 'app_visitor_welcome', methods: ['GET'])]
    public function index(PostRepository $postRepository, ApiFootballService $apiFootballService): Response
    {
        // Je demande a trouver les posts publiés et d'afficher par ordre descendant et je limite à 3
        $posts = $postRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC'], 3);

        // Api football service
        $nextMatch = $apiFootballService->getNextPsgMatch();

        $season = 2023;

        return $this->render('pages/visitor/welcome/index.html.twig', [
            'posts' => $posts,
            'nextMatch' => $nextMatch,
            'season' => $season,
        ]);
    }
}
