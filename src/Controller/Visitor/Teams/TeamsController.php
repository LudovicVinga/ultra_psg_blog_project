<?php

namespace App\Controller\Visitor\Teams;

use App\Services\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TeamsController extends AbstractController
{
    public function __construct(
        private ApiService $apiService,
    ) {
    }

    #[Route('/visitor/teams', name: 'app_visitor_teams', methods: ['GET'])]
    public function index(): Response
    {
        $coach = $this->apiService->getCoach();
        $squadToShow = $this->apiService->excludePlayersOfSquad(['Louis Mouquet', 'Noham Kamara', 'Axel Tape', 'Wassim Slama', 'Quentin Ndjantou']);

        return $this->render('pages/visitor/teams/index.html.twig', [
            'squad' => $squadToShow,
            'coach' => $coach,
        ]);
    }
}
