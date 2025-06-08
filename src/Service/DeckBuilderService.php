<?php

namespace App\Service;

use App\Entity\Deck;
use App\Entity\DeckCard;
use App\Entity\User;
use App\Entity\Card;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use App\Repository\DeckCardRepository;
use App\Exception\DeckNotFoundException;
use App\Exception\CardNotFoundException;
use App\Exception\InvalidCardQuantityException;
use App\Service\ScryfallApiService;

class DeckBuilderService
{
    private $deckRepository;
    private $cardRepository;
    private $deckCardRepository;
    private $scryfallApiService;

    public function __construct(
        DeckRepository $deckRepository,
        CardRepository $cardRepository,
        DeckCardRepository $deckCardRepository,
        ScryfallApiService $scryfallApiService
    ) {
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
        $this->deckCardRepository = $deckCardRepository;
        $this->scryfallApiService = $scryfallApiService;
    }

    public function addCardToDeck(User $user, int $deckId, string $cardName, string $cardId, int $quantity, bool $isSideboard = false): array
    {
        // Buscar el mazo
        $deck = $this->deckRepository->find($deckId);

        // Verificar que el mazo existe y pertenece al usuario
        if (!$deck || $deck->getUser() !== $user) {
            throw new DeckNotFoundException('Mazo no encontrado o no tienes permiso para modificarlo');
        }

        // Buscar la carta
        $card = $this->cardRepository->findIdScryfall($cardId);
        if (!$card) {
            // Añadir la carta a la base de datos si no existe
            $card_new = new Card();
            $card_new->setCardName($cardName);
            $card_new->setIdScryfall($cardId);
            $this->cardRepository->save($card_new, true);

        }

        $card = $this->cardRepository->findIdScryfall($cardId);

        // Validar la cantidad
        if ($quantity <= 0) {
            throw new InvalidCardQuantityException('La cantidad debe ser mayor que 0');
        }

        // Verificar restricciones del formato (ejemplo: máximo 4 copias por carta)
        $format = $deck->getFormat();
        if ($format !== 'commander' && $card->getCardName() !== 'Basic Land') {
            $existingCount = $this->deckCardRepository->countCardInDeck($deck, $card);
            if ($existingCount + $quantity > 4) {
                throw new InvalidCardQuantityException('No se pueden tener más de 4 copias de esta carta en el mazo');
            }
        }

        // Buscar si la carta ya existe en el mazo
        $deckCard = $this->deckCardRepository->findOneBy([
            'deck' => $deck,
            'card' => $card,
        ]);

        // Si ya existe, actualizar cantidad
        if ($deckCard) {
            $deckCard->setCardQuantity($deckCard->getCardQuantity() + $quantity);
        } else {
            // Si no existe, crear nueva relación
            $deckCard = new DeckCard();
            $deckCard->setDeck($deck);
            $deckCard->setCard($card);
            $deckCard->setCardQuantity($quantity);
        }

        // Guardar cambios
        $this->deckCardRepository->save($deckCard, true);

        // Actualizar contadores del mazo
        $totalValue = $this->calculateDeckValue($deck);

        // Devolver los datos actualizados
        return [
            'deck' => $deck,
            'card' => $card,
            'quantity' => $deckCard->getCardQuantity(),
            'deckValue' => $totalValue
        ];
    }

    private function calculateDeckValue(Deck $deck): float
    {
        $totalValue = 0.0;

        // Obtener todas las cartas del mazo
        $deckCards = $this->deckCardRepository->findBy(['deck' => $deck]);

        try {
            // Obtener el valor de cada carta desde la API de Scryfall
            foreach ($deckCards as $deckCard) {
                $card = $deckCard->getCard();
                // Llamar a la api de scrifall para obtener el precio de la carta
                $price = $this->scryfallApiService->getCardPrice($card->getIdScryfall());
                if ($price) {
                    // Calcular el valor total de la carta en el mazo
                    $totalValue += $price * $deckCard->getCardQuantity();
                }
            }
        } catch (\Exception $e) {
            // Manejo de excepciones si no se puede obtener el precio
            throw new \Exception('Error al calcular el valor del mazo: ' . $e->getMessage());
        }

        return $totalValue;
    }

    // Crear un mazo nuevo
    public function createDeck(User $user, string $deckName, string $deckFormat): Deck
    {
        $deck = new Deck();
        $deck->setDeckName($deckName);
        $deck->setFormat($deckFormat);
        $deck->setUser($user);

        // Guardar el mazo en la base de datos
        $this->deckRepository->create($deck, true);

        return $deck;
    }

    // Check if a deck is empty
    public function isDeckEmpty(Deck $deck): bool
    {
        return $this->deckCardRepository->countCardsInDeck($deck) === 0;
    }

    // Edit deck
    public function editDeck(Deck $deck, string $deckName, string $deckFormat): Deck
    {
        $deck->setDeckName($deckName);
        $deck->setFormat($deckFormat);

        // Guardar los cambios en la base de datos
        $this->deckRepository->save($deck, true);

        return $deck;
    }

    /**
     * Actualiza las cartas de un mazo con los cambios proporcionados
     * 
     * @param User $user Usuario propietario del mazo
     * @param Deck $deck Mazo a actualizar
     * @param array $changedCards Array de cartas con cambios
     * @return array Resultado de la operación con contadores y errores
     */
    public function updateDeckCards(User $user, Deck $deck, array $changedCards): array
    {
        $result = [
            'success_count' => 0,
            'errors' => [],
            'deck_value' => 0,
            'total_cards' => 0
        ];

        if (!$user || !$deck) {
            throw new \InvalidArgumentException('Usuario o mazo no válido');
        }

        if (empty($changedCards)) {
            throw new \InvalidArgumentException('No se proporcionaron cartas para actualizar');
        }

        // Verificar que el mazo pertenece al usuario
        if ($deck->getUser() !== $user) {
            throw new \InvalidArgumentException('No tienes permisos para modificar este mazo');
        }

        try {
            foreach ($changedCards as $cardData) {
                try {
                    // Validar estructura de datos de la carta
                    if (!$this->validateDeckCardData($cardData)) {
                        $result['errors'][] = 'Datos de carta inválidos: ' . json_encode($cardData);
                        continue;
                    }

                    $cardId = $cardData['card_id'] ?? null;
                    $newQuantity = (int)($cardData['quantity'] ?? 0);

                    // Buscar la entrada en el mazo
                    $deckCard = null;
                    if ($cardId) {
                        // Búsqueda por ID de carta (compatibilidad)
                        $card = $this->cardRepository->find($cardId);
                        if ($card) {
                            $deckCard = $this->deckCardRepository->findOneByDeckAndCard($deck, $card);
                        }
                    }

                    // Si la nueva cantidad es 0 o menor, eliminar la carta del mazo
                    if ($newQuantity <= 0) {
                        $this->deckCardRepository->remove($deckCard, true);
                        $result['success_count']++;
                        continue;
                    }

                    // Actualizar cantidad si es diferente
                    if ($newQuantity !== $deckCard->getCardQuantity()) {
                        $deckCard->setCardQuantity($newQuantity);
                    }

                    // Guardar los cambios
                    $this->deckCardRepository->save($deckCard, true);

                    $result['success_count']++;

                } catch (\Exception $e) {
                    $identifier = $cardData['deck_card_id'] ?? $cardData['card_id'] ?? 'desconocido';
                    $result['errors'][] = "Error al actualizar carta {$identifier}: " . $e->getMessage();
                }
            }

            // Calcular estadísticas actualizadas del mazo
            $result['deck_value'] = $this->calculateDeckValue($deck);
            $result['total_cards'] = $this->getTotalCards($deck);

        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar el mazo: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Valida la estructura de datos de una carta para actualización en mazo
     * 
     * @param array $cardData Datos de la carta
     * @return bool True si los datos son válidos
     */
    private function validateDeckCardData(array $cardData): bool
    {
        // Verificar que existe el identificador de carta del mazo o carta
        $hasDeckCardId = isset($cardData['deck_card_id']);
        $hasCardId = isset($cardData['card_id']);

        if (!$hasDeckCardId && !$hasCardId) {
            return false;
        }

        // Verificar que la cantidad es un número válido si se proporciona
        if (isset($cardData['quantity']) && (!is_numeric($cardData['quantity']) || $cardData['quantity'] < 0)) {
            return false;
        }

        // Verificar que el estado sideboard es válido si se proporciona
        if (isset($cardData['is_sideboard']) && !is_bool($cardData['is_sideboard']) && !in_array($cardData['is_sideboard'], [0, 1, '0', '1', true, false])) {
            return false;
        }

        return true;
    }


    /**
     * Obtiene el número total de cartas en el mazo
     * 
     * @param Deck $deck Mazo
     * @return int Total de cartas
     */
    private function getTotalCards(Deck $deck): int
    {
        return $this->deckCardRepository->countCardsInDeck($deck);
    }

    /**
     * Cuenta el total de cartas en un mazo
     * 
     * @param Deck $deck Mazo
     * @return int Número total de cartas
     */
    public function countCardsInDeck(Deck $deck): int
    {
        $deckCards = $this->deckCardRepository->findBy(['deck' => $deck]);
        $totalCards = 0;

        foreach ($deckCards as $deckCard) {
            $totalCards += $deckCard->getCardQuantity();
        }

        return $totalCards;
    }
}
