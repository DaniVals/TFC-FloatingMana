<?php

namespace App\Service;

use App\Entity\Collection;
use App\Entity\User;
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

    public function getCardPrice(string $id): float
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
            return (float)($data['prices']['eur'] ?? 0);  
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface | DecodingExceptionInterface $e) {
            throw new \Exception('Error during API request: ' . $e->getMessage());
        }
    }

    // Trend value
    /**
     * Obtiene el valor de tendencia de una colección.
     *
     * @param Collection[] $collection
     * @return float
     */
    public function getTrendValue(array $collection): float
    {
        // Sacar los precios de scryfall de cada carta de la colección
        $totalPrice = 0.0;
        $cardCount = count($collection);
        if ($cardCount === 0) {
            return 0.0; // Evitar división por cero
        }
        foreach ($collection as $card) {
            // Sumar los precios de todas las cartas de la colección teniendo en cuenta que pueden ser diferentes versiones de la misma carta y la cantidaad de copias
            $cardPrice = $this->getCardPrice($card->getCard()->getIdScryfall());
            $totalPrice += $cardPrice * $card->getQuantity();
        }
        // Calcular el valor de tendencia
        return $totalPrice; // Retorna el valor de tendencia

    }


    // Get diffrence
    /**
     * Obtiene la diferencia de valor entre dos colecciones.
     *
     * @param Collection[] $collection1
     * @param Collection[] $collection2
     * @return float
     */
    public function getDifferenceOfPrice(float $trend_price, float $collection_price): float
    {
        // Devolver el porcentaje de diferencia entre el valor de tendencia y el valor de la colección
        if ($collection_price === 0) {
            return 0; // Evitar división por cero
        }
        $difference = $trend_price - $collection_price;
        return ($difference / $collection_price) * 100; // Retorna el porcentaje de diferencia
    }
}
