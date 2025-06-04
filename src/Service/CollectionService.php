<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Collection;
use App\Entity\User;
use App\Entity\State;
use App\Repository\CardRepository;
use App\Repository\UserCollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CollectionService
{
    private $entityManager;
    private $collectionRepository;
    private $cardRepository;
    private $security;
    private $scryfallApiService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserCollectionRepository $collectionRepository,
        CardRepository $cardRepository,
        ScryfallApiService $scryfallApiService,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->collectionRepository = $collectionRepository;
        $this->cardRepository = $cardRepository;
        $this->scryfallApiService = $scryfallApiService;
        $this->security = $security;
    }

    /**
     * Obtiene la colección completa del usuario actual
     */
    public function getUserCollection(User $user): array
    {
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        return $this->collectionRepository->findByUser($user);
    }

    /**
     * Obtiene una carta específica de la colección del usuario
     */
    public function getCardFromCollection(int $cardId): ?Collection
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        return $this->collectionRepository->findOneBy([
            'user' => $user,
            'card' => $cardId
        ]);
    }

    /**
     * Añade una carta a la colección del usuario
     */
    public function addCardToCollection(string $cardId, int $quantity, int $price, int $foil, int $state): ?Collection
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor que cero');
        }

        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $card = $this->cardRepository->findIdScryfall($cardId);
        if (!$card) {
            // Crear una nueva carta si no existe
            $cardData = $this->scryfallApiService->getCardById($cardId);
            if (!$cardData) {
                throw new \Exception('Carta no encontrada en Scryfall');
            }
            $card = new Card();
            $card->setCardName($cardData['name']);
            $card->setIdScryfall($cardData['id']);
            // Configurar más campos según la estructura de la entidad Card
            $this->entityManager->persist($card);
            $this->entityManager->flush();
        }

        $state = $this->entityManager->getRepository(State::class)->findOneBy(['idState' => $state]);
        if (!$state) {
            throw new \Exception('Estado de la carta no válido');
        }

        // Verificar si ya existe en la colección
        $collectionItem = $this->collectionRepository->findOneByCardAndUser($card, $user);

        if ($collectionItem) {
            // Si ya existe, actualizamos la cantidad
            $collectionItem->setQuantity($collectionItem->getQuantity() + $quantity);
        } else {
            // Si no existe, creamos un nuevo registro
            $collectionItem = new Collection();
            $collectionItem->setUser($user);
            $collectionItem->setCard($card);
            $collectionItem->setQuantity($quantity);
            $collectionItem->setpurchasePrice($price);
            $collectionItem->setisFoil($foil);
            $collectionItem->setState($state);
        }

        $this->entityManager->persist($collectionItem);
        $this->entityManager->flush();

        return $collectionItem;
    }

    /**
     * Actualiza la cantidad de una carta en la colección
     */
    public function updateCardQuantity(int $cardId, int $quantity): Collection
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('La cantidad no puede ser negativa');
        }

        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $collectionItem = $this->collectionRepository->findOneBy([
            'user' => $user,
            'card' => $cardId
        ]);

        if (!$collectionItem) {
            throw new \Exception('Carta no encontrada en tu colección');
        }

        if ($quantity === 0) {
            // Si la cantidad es 0, eliminamos la carta de la colección
            $this->entityManager->remove($collectionItem);
            $this->entityManager->flush();
            return $collectionItem;
        }

        $collectionItem->setQuantity($quantity);
        $this->entityManager->flush();
        return $collectionItem;
    }

    /**
     * Elimina una carta de la colección
     */
    public function removeCardFromCollection(int $cardId): void
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $collectionItem = $this->collectionRepository->findOneBy([
            'user' => $user,
            'card' => $cardId
        ]);

        if (!$collectionItem) {
            throw new \Exception('Carta no encontrada en tu colección');
        }

        $this->entityManager->remove($collectionItem);
        $this->entityManager->flush();
    }

    /**
     * Busca cartas en la colección del usuario por nombre
     */
    public function searchInCollection(string $query): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        return $this->collectionRepository->searchByNameAndUser($query, $user);
    }

    /**
     * Obtiene estadísticas de la colección del usuario
     */
    public function getCollectionStats(): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $collection = $this->collectionRepository->findByUser($user);
        $totalCards = 0;
        $uniqueCards = count($collection);
        $estimatedValue = 0;

        foreach ($collection as $item) {
            $totalCards += $item->getQuantity();
            // Asumiendo que la entidad Card tiene un campo price
            $estimatedValue += ($item->getCard()->getPrice() ?? 0) * $item->getQuantity();
        }

        return [
            'total_cards' => $totalCards,
            'unique_cards' => $uniqueCards,
            'estimated_value' => $estimatedValue
        ];
    }

    /**
     * Importa una lista de cartas a la colección
     */
    public function importCardList(string $cardList): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $lines = explode("\n", $cardList);
        $results = [
            'success' => [],
            'errors' => []
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Formato esperado: "2x Card Name [SET]" o "Card Name"
            $quantity = 1;
            $cardName = $line;

            // Extraer cantidad si está en formato "2x Card Name"
            if (preg_match('/^(\d+)x\s+(.+)$/', $line, $matches)) {
                $quantity = (int) $matches[1];
                $cardName = $matches[2];
            }

            // Extraer set si está presente
            $set = null;
            if (preg_match('/^(.+)\s+\[([A-Z0-9]{3,4})\]$/', $cardName, $matches)) {
                $cardName = $matches[1];
                $set = $matches[2];
            }

            try {
                // Buscar la carta por nombre (y posiblemente set)
                $cardData = $this->scryfallApiService->searchCard($cardName, $set);
                if (!$cardData) {
                    $results['errors'][] = "Carta no encontrada: $line";
                    continue;
                }

                // Buscar si la carta ya existe en la base de datos
                $card = $this->cardRepository->findOneByScryfallId($cardData['id']);
                if (!$card) {
                    $card = new Card();
                    $card->setName($cardData['name']);
                    $card->setScryfallId($cardData['id']);
                    // Configurar más campos según la estructura de la entidad Card
                    
                    $this->entityManager->persist($card);
                    $this->entityManager->flush();
                }

                // Añadir a la colección
                $collectionItem = $this->collectionRepository->findOneBy([
                    'user' => $user,
                    'card' => $card
                ]);

                if ($collectionItem) {
                    $collectionItem->setQuantity($collectionItem->getQuantity() + $quantity);
                } else {
                    $collectionItem = new Collection();
                    $collectionItem->setUser($user);
                    $collectionItem->setCard($card);
                    $collectionItem->setQuantity($quantity);
                    $this->entityManager->persist($collectionItem);
                }

                $this->entityManager->flush();
                $results['success'][] = "{$quantity}x {$cardName}";
            } catch (\Exception $e) {
                $results['errors'][] = "Error al procesar $line: " . $e->getMessage();
            }
        }
        return $results;
    }

    public function getUserCollectionArray(User $user): array
    {
        $collections = $this->entityManager->getRepository(Collection::class)->findBy(['user' => $user]);

        return array_map(fn(Collection $c) => $c->toArray(), $collections);
    }

}
