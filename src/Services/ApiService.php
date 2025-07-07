<?php

namespace App\Services;

class ApiService
{
    private string $uri = 'https://api.football-data.org/v4/competitions/FL1/teams';
    // private string $uri = 'https://api.football-data.org/v4/teams/524';

    private string $apiKey;

    private mixed $matches;

    public function __construct()
    {
        $this->loadApiKey();
        $this->loadMatches();
    }

    public function loadApiKey(): void
    {
        $this->apiKey = $_ENV['FOOTBALL_API_KEY'];
    }

    public function loadMatches(): void
    {
        $uri = $this->uri;

        $reqPrefs['http']['method'] = 'GET';
        $reqPrefs['http']['header'] = "X-Auth-Token: {$this->apiKey}";
        $stream_context = stream_context_create($reqPrefs);
        $response = file_get_contents($uri, false, $stream_context);
        $this->matches = json_decode($response);
    }

    public function getMatches(): mixed
    {
        return $this->matches;
    }

    public function getSquad(): mixed
    {
        return $this->matches->teams[8]->squad;
    }

    public function getCoach(): object
    {
        return $this->matches->teams[8]->coach;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Cette fonction permet de garder uniquement les joueurs a afficher.
     *
     * @param array<int, string> $playersListToDelete
     */
    public function excludePlayersOfSquad(array $playersListToDelete = []): mixed
    {
        $playersListToShow = [];

        foreach ($this->getSquad() as $player) {
            if (!in_array($player->name, $playersListToDelete)) {
                $playersListToShow[] = $player;
            }
        }

        return $playersListToShow;
    }
}
