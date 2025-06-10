<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Collection;
use App\Entity\User;
use App\Entity\State;
use App\Repository\CardRepository;
use App\Repository\UserCollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Double;
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
    public function addCardToCollection(string $cardId, int $quantity, float $price, int $foil, int $state): ?Collection
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
            } $card = new Card();
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
        $collectionItems = $this->collectionRepository->findOneByCardAndUser($card, $user);

        // Iterar por la collectionItems para que, por cada carta, se verifique si ya existe
        foreach ($collectionItems as $collectionItem) {
            if ($collectionItem) {
                // Si la carta es diferente por cualquier motivo, crear una nueva entrada
                if ($collectionItem->getState() !== $state || $collectionItem->getIsFoil() !== $foil || $collectionItem->getPurchasePrice() !== $price) {
                    $collectionItem = new Collection();
                    $collectionItem->setUser($user);
                    $collectionItem->setCard($card);
                    $collectionItem->setQuantity($quantity);
                    $collectionItem->setPurchasePrice($price);
                    $collectionItem->setIsFoil($foil);
                    $collectionItem->setState($state);
                } else {
                    // Si ya existe, solo actualizar la cantidad y precio
                    $collectionItem->setQuantity($collectionItem->getQuantity() + $quantity);
                }
            }
        }

        if (!isset($collectionItem)) {
            // Si no existe, crear una nueva entrada en la colección
            $collectionItem = new Collection();
            $collectionItem->setUser($user);
            $collectionItem->setCard($card);
            $collectionItem->setQuantity($quantity);
            $collectionItem->setPurchasePrice($price);
            $collectionItem->setIsFoil($foil);
            $collectionItem->setState($state);
        }

        $this->entityManager->persist($collectionItem);
        $this->entityManager->flush();

        return $collectionItem;
    }

    /**
 * Actualiza la colección del usuario con los cambios proporcionados
 * 
 * @param User $user Usuario propietario de la colección
 * @param array $changedCards Array de cartas con cambios
 * @return array Resultado de la operación con contadores y errores
 */
    public function updateUserCollection(User $user, array $changedCards): array
    {
        $result = [
            'success_count' => 0,
            'errors' => []
        ];

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no válido');
        }

        if (empty($changedCards)) {
            throw new \InvalidArgumentException('No se proporcionaron cartas para actualizar');
        }

        // Iniciar transacción para asegurar consistencia
        $this->entityManager->beginTransaction();

        try {
            foreach ($changedCards as $cardData) {
                try {
                    // Validar estructura de datos de la carta
                    if (!$this->validateCardData($cardData)) {
                        $result['errors'][] = 'Datos de carta inválidos: ' . json_encode($cardData);
                        continue;
                    }

                    // Soportar búsqueda por collection_id (recomendado) o card_id (compatibilidad)
                    $collectionId = $cardData['card_id'] ?? $cardData['idCollection'] ?? null;
                    $newQuantity = (int)($cardData['quantity'] ?? 0);

                    // Buscar la entrada en la colección
                    if ($collectionId) {
                        // Búsqueda por ID de colección (recomendado)
                        $collectionItem = $this->collectionRepository->findOneBy([
                            'idCollection' => $collectionId,
                            'user' => $user
                        ]);
                    } else {
                        $result['errors'][] = 'Debe proporcionar collection_id o card_id';
                        continue;
                    }

                    if (!$collectionItem) {
                        $identifier = $collectionId ?? $cardId ?? 'desconocido';
                        $type = $collectionId ? 'colección' : 'carta';
                        $result['errors'][] = "Entrada de {$type} con ID {$identifier} no encontrada o no pertenece al usuario";
                        continue;
                    }

                    // Si la nueva cantidad es 0 o menor, eliminar la carta de la colección
                    if ($newQuantity <= 0) {
                        $this->entityManager->remove($collectionItem);
                        $result['success_count']++;
                        continue;
                    }

                    // Actualizar cantidad
                    if ($newQuantity !== $collectionItem->getQuantity()) {
                        $collectionItem->setQuantity($newQuantity);
                    }


                    // Persistir los cambios
                    $this->entityManager->persist($collectionItem);
                    $result['success_count']++;

                } catch (\Exception $e) {
                    $identifier = $cardData['collection_id'] ?? $cardData['idCollection'] ?? $cardData['card_id'] ?? $cardData['idCard'] ?? 'desconocido';
                    $result['errors'][] = "Error al actualizar entrada {$identifier}: " . $e->getMessage();
                }
            }

            // Ejecutar todos los cambios
            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \Exception('Error al actualizar la colección: ' . $e->getMessage());
        }

        return $result;
    }

    /**
 * Valida la estructura de datos de una carta para actualización
 * 
 * @param array $cardData Datos de la carta
 * @return bool True si los datos son válidos
 */
    private function validateCardData(array $cardData): bool
    {
        // Verificar que existe el identificador de colección o carta
        $hasCollectionId = isset($cardData['collection_id']) || isset($cardData['idCollection']);
        $hasCardId = isset($cardData['card_id']) || isset($cardData['idCard']);

        if (!$hasCollectionId && !$hasCardId) {
            return false;
        }

        // Verificar que la cantidad es un número válido si se proporciona
        if (isset($cardData['quantity']) && (!is_numeric($cardData['quantity']) || $cardData['quantity'] < 0)) {
            return false;
        }

        // Verificar que el precio es válido si se proporciona
        if (isset($cardData['purchase_price']) && !is_numeric($cardData['purchase_price'])) {
            return false;
        }
        if (isset($cardData['purchasePrice']) && !is_numeric($cardData['purchasePrice'])) {
            return false;
        }

        // Verificar que el estado foil es válido si se proporciona
        if (isset($cardData['is_foil']) && !in_array($cardData['is_foil'], [0, 1, '0', '1'])) {
            return false;
        }
        if (isset($cardData['isFoil']) && !in_array($cardData['isFoil'], [0, 1, '0', '1'])) {
            return false;
        }

        return true;
    }



    /*
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
            $estimatedValue += ($item->getPurchasePrice() ?? 0) * $item->getQuantity();
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

    public function fetchMostValuableCards(User $user, int $limit = 5): array
    {
        $collections = $this->collectionRepository->findMostValuableCardsByUser($user, $limit);
        return $collections;
    }
}
