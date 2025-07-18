<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiFootballService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * Cette méthode retourne les prochains matchs du psg dans un tableau
     * Un tableau dont les clés sont des string et les valeurs de n'importe quel type.
     *
     * @return array<string, mixed>|null
     */
    public function getNextPsgMatch(): ?array
    {
        // Ajoute &season=2024 dans l'URL (modifie l'année si besoin)
        $season = 2023; // ajuste si la nouvelle saison commence (ex: 2025)
        $url = 'https://v3.football.api-sports.io/fixtures?team=85&status=NS&season='.$season;

        $response = $this->client->request('GET', $url, [
            'headers' => [
                'x-apisports-key' => $this->apiKey,
            ],
        ]);

        $data = $response->toArray(false);
        // dd($data);
        $futureMatches = $data['response'] ?? [];

        if (empty($futureMatches)) {
            // Aucun match à venir trouvé
            return null;
        }

        // Trie les matchs par date croissante (du plus proche au plus lointain)
        usort($futureMatches, function ($a, $b) {
            return strtotime($a['fixture']['date']) <=> strtotime($b['fixture']['date']);
        });

        // Retourne le premier match (= prochain)
        return $futureMatches[0];
    }
}
