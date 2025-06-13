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
        $deckCard = $this->deckCardRepository->findOneByDeckAndCard($deck, $card);

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
    public function createDeck(User $user, string $deckName, string $deckFormat, string $cover): Deck
    {
        $deck = new Deck();
        $deck->setDeckName($deckName);
        $deck->setFormat($deckFormat);
        $deck->setUser($user);
        $deck->setCoverImg($cover);

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
    public function editDeck(Deck $deck, string $deckName, string $deckFormat, string $cover): Deck
    {
        $deck->setDeckName($deckName);
        $deck->setFormat($deckFormat);
        $deck->setCoverImg($cover);

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
    // En DeckBuilderService.php - Reemplaza el método updateDeckCards

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

                    // CORRECCIÓN: card_id en el JSON es realmente idDeckCard
                    $deckCardId = $cardData['card_id'] ?? null;  // Este es el idDeckCard
                    $newQuantity = (int)($cardData['quantity'] ?? 0);

                    // Buscar directamente por idDeckCard
                    $deckCard = null;
                    if ($deckCardId) {
                        $deckCard = $this->deckCardRepository->find($deckCardId);

                        // Verificar que la DeckCard encontrada pertenece al mazo correcto
                        if ($deckCard && $deckCard->getDeck()->getIdDeck() !== $deck->getIdDeck()) {
                            $result['errors'][] = "La carta ID {$deckCardId} no pertenece al mazo {$deck->getIdDeck()}";
                            continue;
                        }
                    }

                    // Si no se encontró la DeckCard
                    if (!$deckCard) {
                        $result['errors'][] = "No se encontró la entrada DeckCard con ID {$deckCardId}";
                        continue;
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
                        $this->deckCardRepository->save($deckCard, true);
                    }

                    $result['success_count']++;

                } catch (\Exception $e) {
                    $identifier = $cardData['card_id'] ?? 'desconocido';
                    $result['errors'][] = "Error al actualizar DeckCard ID {$identifier}: " . $e->getMessage();
                }
            }

            // Calcular valores finales del mazo
            $result['deck_value'] = $this->calculateDeckValue($deck);
            $result['total_cards'] = $this->countCardsInDeck($deck);

        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar el mazo: ' . $e->getMessage());
        }

        return $result;
    }

    /**
 * Valida la estructura de datos de una carta para actualización en mazo
 */
    private function validateDeckCardData(array $cardData): bool
    {
        // Verificar que existe card_id (que es realmente idDeckCard)
        if (!isset($cardData['card_id']) || !is_numeric($cardData['card_id'])) {
            return false;
        }

        // Verificar que la cantidad es un número válido si se proporciona
        if (isset($cardData['quantity']) && (!is_numeric($cardData['quantity']) || $cardData['quantity'] < 0)) {
            return false;
        }

        return true;
    }

    /**
     * Cuenta el total de cartas en un mazo
     * 
     * @param Deck $deck Mazo
     * @return int Número total de cartas
     */
    public function countCardsInDeck(Deck $deck): int
    {
        return $this->deckCardRepository->countCardsInDeck($deck);
    }
}
