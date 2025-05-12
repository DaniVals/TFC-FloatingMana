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
    
    public function addCardToDeck(User $user, int $deckId, string $cardName, string $cardId, string $quantity, bool $isSideboard = false): array
    {
        // Buscar el mazo
        $deck = $this->deckRepository->find($deckId);
        
        // Verificar que el mazo existe y pertenece al usuario
        if (!$deck || $deck->getUser() !== $user) {
            throw new DeckNotFoundException('Mazo no encontrado o no tienes permiso para modificarlo');
        }
        
        // Buscar la carta
        $card = $this->cardRepository->findId($cardId);
        if (!$card) {
            // Añadir la carta a la base de datos si no existe
            $card_new = new Card();
            $card_new->setCardName($cardName);
            $card_new->setIdScryfall($cardId);
            $this->cardRepository->save($card_new, true);

        }

        $card = $this->cardRepository->findId($cardId);


        
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
        // $mainboardCount = $this->deckCardRepository->countCardsInMainboard($deck);
        // $sideboardCount = $this->deckCardRepository->countCardsInSideboard($deck);
        $totalValue = $this->calculateDeckValue($deck);
        
        // Devolver los datos actualizados
        return [
            'deck' => $deck,
            'card' => $card,
            'quantity' => $deckCard->getCardQuantity(),
            // 'cardCount' => $mainboardCount + $sideboardCount,
            // 'mainboardCount' => $mainboardCount,
            // 'sideboardCount' => $sideboardCount,
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
    public function createDeck(User $user, string $deckName, string $type): Deck
    {
        $deck = new Deck();
        $deck->setDeckName($deckName);
        $deck->setFormat($type);
        $deck->setUser($user);
        
        // Guardar el mazo en la base de datos
        $this->deckRepository->createDeck($deck, true);
        
        return $deck;
    }

    // Check if a deck is empty
    public function isDeckEmpty(Deck $deck): bool
    {
        return $this->deckCardRepository->countCardsInDeck($deck) === 0;
    }
}
