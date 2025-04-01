<?php
namespace App\Service;

class DeckBuilderService {
    private $deckRepository;
    private $cardRepository;


    public function buildDeck($deckData)
    {
        return $deckData;
    }

    // Método para obtener un mazo por su ID
    public function getDeckById($id) {
        return $this->deckRepository->getDeckById($id);
    }

    // Método para obtener una carta por su ID
    public function getCardById($id) {
        return $this->cardRepository->getCardById($id);
    }
}
