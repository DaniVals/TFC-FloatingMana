<?php
namespace App\Service;

use App\Repository\UserCollectionRepository;
use App\Repository\DeckRepository;
use App\Repository\CardRepository;

class CollectionService {

    private $userCollectionRepository;
    private $deckRepository;
    private $cardRepository;

    public function __construct(
        UserCollectionRepository $userCollectionRepository,
        DeckRepository $deckRepository,
        CardRepository $cardRepository
    ) {
        $this->userCollectionRepository = $userCollectionRepository;
        $this->deckRepository = $deckRepository;
        $this->cardRepository = $cardRepository;
    }

    // Método para obtener la colección de un usuario por su ID
    public function getUserCollectionById($id) {
        return $this->userCollectionRepository->getUserCollectionById($id);
    }
}
