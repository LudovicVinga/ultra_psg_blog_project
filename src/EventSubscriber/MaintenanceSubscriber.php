<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] protected string $projectDir,
        private Environment $twig,
    ) {
    }

    public function onRequestEvent(RequestEvent $event): void
    {
        $maintenanceFile = $this->projectDir.'/var/maintenance.lock';

        if (!file_exists($maintenanceFile)) {
            return;
        }

        // On prépare la page affichée a l'utilisateur
        $template = $this->twig->render('pages/maintenance/show.html.twig');

        // On prépare la réponse a retourner en précisant aux robots des moteurs de recherche la nature de l'erreur (code 503)
        $response = new Response($template, Response::HTTP_SERVICE_UNAVAILABLE);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequestEvent',
        ];
    }
}
