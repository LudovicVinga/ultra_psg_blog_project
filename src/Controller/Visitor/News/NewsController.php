<?php

namespace App\Controller\Visitor\News;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_visitor_news', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/news/index.html.twig', [
        ]);
    }
}
