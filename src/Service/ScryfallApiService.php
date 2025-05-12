<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class ScryfallApiService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getRandomCard(): array
    {
        $url = 'https://api.scryfall.com/cards/random';

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'MyMTGApp/1.0',
                    'Accept' => 'application/json;q=0.9,*/*;q=0.8',
                ]
            ]);

            // Verificamos que la respuesta sea correcta (status code 2xx)
            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Error fetching random card: " . $response->getStatusCode());
            }

            return $response->toArray(); // Devuelve el array decodificado de JSON
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface | DecodingExceptionInterface $e) {
            // Manejo de excepciones específicas de la API
            throw new \Exception('Error during API request: ' . $e->getMessage());
        }
    }

    public function getCardById(string $id): array {
        $url = 'https://api.scryfall.com/cards/' . $id;

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'MyMTGApp/1.0',
                    'Accept' => 'application/json;q=0.9,*/*;q=0.8',
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Error fetching card by ID: " . $response->getStatusCode());
            }

            return $response->toArray(); // Devuelve el array decodificado de JSON
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface | DecodingExceptionInterface $e) {
            throw new \Exception('Error during API request: ' . $e->getMessage());
        }
    }

    public function searchCards(string $nombre): array
    {
        $url = 'https://api.scryfall.com/cards/search?q=' . urlencode($nombre) . '&unique=prints';

        // Buscar carta que empieza por el nombre introducido
        // $url = 'https://api.scryfall.com/cards/search?q=' . urlencode($nombre) . '&unique=prints&order=name&dir=asc';

        try {
            // Realizamos la solicitud GET con el cliente HTTP de Symfony
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'MyMTGApp/1.0',
                    'Accept' => 'application/json;q=0.9,*/*;q=0.8',
                ]
            ]);

            // Verificamos que la respuesta sea correcta (status code 2xx)
            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Error fetching search results: " . $response->getStatusCode());
            }

            // Decodificamos el contenido de la respuesta
            $data = $response->toArray();

            // Si no hay resultados bajo 'data', devolver el array completo como único elemento
            return $data['data'] ?? [$data]; // Si no hay cartas, devuelve el array completo como único elemento
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface | DecodingExceptionInterface $e) {
            // Manejo de errores
            throw new \Exception('Error during API request: ' . $e->getMessage());
        }
    }

    public function getCardPrice(string $id): int
    {
        $url = 'https://api.scryfall.com/cards/' . $id;

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'MyMTGApp/1.0',
                    'Accept' => 'application/json;q=0.9,*/*;q=0.8',
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Error fetching card price: " . $response->getStatusCode());
            }

            $data = $response->toArray();
            return (int)($data['prices']['eur'] ?? 0);  
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface | DecodingExceptionInterface $e) {
            throw new \Exception('Error during API request: ' . $e->getMessage());
        }
    }
}
