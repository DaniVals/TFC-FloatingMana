<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Deck;
use App\Entity\DeckCard;
use App\Entity\Card;
use App\Entity\User;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/vals/deck', name: 'vals_deck_')]
class DeckControllerDebug extends AbstractController {
    private DeckRepository $deckRepository;
    private EntityManagerInterface $entityManager;
	private Deck $deck;

    public function __construct(DeckRepository $deckRepository, EntityManagerInterface $entityManager) {
		$this->deckRepository = $deckRepository;
		$this->entityManager = $entityManager;

		$this->deck = new Deck();
		$this->deck->setDeckName("nombre mazo");
		$this->deck->setType("tipo mazo");
		$this->deck->setUser(new User());

		$deckCards = [];

		$deckCard = new DeckCard();
			$card = new Card();
			$card->setCardName("carta sin id (para ver fallos)");
			$card->setIdScryfall("111"); // si es "" peta el enlace
		$deckCard->setCard($card);
		$deckCards[] = $deckCard;

		$deckCard = new DeckCard();
			$card = new Card();
			$card->setCardName("counter spell");
			$card->setIdScryfall("5d93b770-dc46-46ad-aefe-282dac8cc246");
		$deckCard->setCard($card);
		$deckCards[] = $deckCard;

		$deckCard = new DeckCard();
			$card = new Card();
			$card->setCardName("isshin");
			$card->setIdScryfall("1bf8b008-3a7c-4b6d-8c18-263a576f0d64");
		$deckCard->setCard($card);
		$deckCards[] = $deckCard;

		$deckCard = new DeckCard();
			$card = new Card();
			$card->setCardName("Spinning Wheel Kick");
			$card->setIdScryfall("5f36ddd7-82c1-45ef-a966-ae2a34c540e1");
		$deckCard->setCard($card);
		$deckCards[] = $deckCard;

		$deckCard = new DeckCard();
			$card = new Card();
			$card->setCardName("Yotia Declares War");
			$card->setIdScryfall("3bd9e99a-ae8c-4323-aa86-b19288c877d4");
		$deckCard->setCard($card);
		$deckCards[] = $deckCard;

		$this->deck->setDeckCards($deckCards);
    }

    #[Route('/', name: 'index')]
    public function index() {
		return $this->render('deckManagement/index.html.twig');
    }

    #[Route('/{id}', name: 'show')]
    public function show(int $id) {
		return $this->render('deckManagement/deck.html.twig', [
			'deck' => $this->deck,
		]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Deck $deck) {
		return $this->render('deckManagement/edit.html.twig', [
			'deck' => $this->deck,
		]);
    }

}
