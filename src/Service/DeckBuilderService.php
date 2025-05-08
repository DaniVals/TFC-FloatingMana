<?php

namespace App\Service;

use App\Entity\Deck;
use App\Entity\DeckCard;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use App\Repository\DeckCardRepository;
use App\Exception\DeckNotFoundException;
use App\Exception\CardNotFoundException;
use App\Exception\InvalidCardQuantityException;

class DeckBuilderService
{
    private $deckRepository;
    private $cardRepository;
    private $deckCardRepository;
    
    public function __construct(
        DeckRepository $deckRepository,
        CardRepository $cardRepository,
        DeckCardRepository $deckCardRepository
    ) {
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
        $this->deckCardRepository = $deckCardRepository;
    }
    
    public function addCardToDeck(User $user, int $deckId, string $cardId, int $quantity, bool $isSideboard = false): array
    {
        // Buscar el mazo
        $deck = $this->deckRepository->find($deckId);
        
        // Verificar que el mazo existe y pertenece al usuario
        if (!$deck || $deck->getUser() !== $user) {
            throw new DeckNotFoundException('Mazo no encontrado o no tienes permiso para modificarlo');
        }
        
        // Buscar la carta
        $card = $this->cardRepository->find($cardId);
        if (!$card) {
            throw new CardNotFoundException('Carta no encontrada');
        }
        
        // Validar la cantidad
        if ($quantity <= 0) {
            throw new InvalidCardQuantityException('La cantidad debe ser mayor que 0');
        }
        
        // Verificar restricciones del formato (ejemplo: máximo 4 copias por carta)
        $format = $deck->getFormat();
        if ($format !== 'commander' && $card->getName() !== 'Basic Land') {
            $existingCount = $this->deckCardRepository->countCardInDeck($deck, $card);
            if ($existingCount + $quantity > 4) {
                throw new InvalidCardQuantityException('No se pueden tener más de 4 copias de esta carta en el mazo');
            }
        }
        
        // Buscar si la carta ya existe en el mazo
        $deckCard = $this->deckCardRepository->findOneBy([
            'deck' => $deck,
            'card' => $card,
            'isSideboard' => $isSideboard
        ]);
        
        // Si ya existe, actualizar cantidad
        if ($deckCard) {
            $deckCard->setQuantity($deckCard->getQuantity() + $quantity);
        } else {
            // Si no existe, crear nueva relación
            $deckCard = new DeckCard();
            $deckCard->setDeck($deck);
            $deckCard->setCard($card);
            $deckCard->setQuantity($quantity);
            $deckCard->setIsSideboard($isSideboard);
        }
        
        // Guardar cambios
        $this->deckCardRepository->save($deckCard, true);
        
        // Actualizar contadores del mazo
        $mainboardCount = $this->deckCardRepository->countCardsInMainboard($deck);
        $sideboardCount = $this->deckCardRepository->countCardsInSideboard($deck);
        $totalValue = $this->calculateDeckValue($deck);
        
        // Actualizar fecha de modificación
        $deck->setUpdatedAt(new \DateTime());
        $this->deckRepository->save($deck, true);
        
        // Devolver los datos actualizados
        return [
            'deck' => $deck,
            'card' => $card,
            'quantity' => $deckCard->getQuantity(),
            'isSideboard' => $isSideboard,
            'cardCount' => $mainboardCount + $sideboardCount,
            'mainboardCount' => $mainboardCount,
            'sideboardCount' => $sideboardCount,
            'deckValue' => $totalValue
        ];
    }
    
    private function calculateDeckValue(Deck $deck): float
    {
        // Calcular el valor total del mazo basado en los precios de las cartas
        // ...
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
