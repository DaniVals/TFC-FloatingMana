<?php
namespace App\Controller;

use App\Service\ScryfallApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Deck;
use App\Entity\DeckCard;
use App\Entity\Card;

class CollectionController extends AbstractController {
    private ScryfallApiService $scryfallApiService;
    public function __construct(ScryfallApiService $scryfallApiService)
    {
        $this->scryfallApiService = $scryfallApiService;
    }

    #[Route('/testing/vals/collection', name: 'testing_vals_collection')]
    public function collectionRender(): Response
    {
		$cards = [];
		for ($i = 0; $i < 5; $i++) {
			$cards[] = $this->scryfallApiService->getRandomCard();
		}
		$user = [
			"collection" => $cards
		];
        return $this->render('collectionManagement\collection.html.twig', ['user' => $user]);
    }

    #[Route('/testing/vals/deck', name: 'testing_vals_deck')]
    public function vals_deck()
    {
		$deck = new Deck();
		$deck->setDeckName('Default Name');
		$deck->setType('Default Type');
		
		$deckCards = [];

		$deckCard  = new DeckCard();
		$card1 = new Card();
		$card1->setCardName("Isshin 1");
		$card1->setIdScryfall("a062a004-984e-4b62-960c-af7288f7a3e9");
		$deckCard->setCard($card1);
		$deckCards[] = $deckCard;
		
		$deckCard  = new DeckCard();
		$card2 = new Card();
		$card2->setCardName("doble cara 1");
		$card2->setIdScryfall("4b4390f4-451f-4575-96e0-dc4dcb45ad8f");
		$deckCard->setCard($card2);
		$deckCards[] = $deckCard;
		
		$deckCard  = new DeckCard();
		$card3 = new Card();
		$card3->setCardName("doble cara 2");
		$card3->setIdScryfall("047f196b-a9d1-4cd3-b665-3b304cc59767");
		$deckCard->setCard($card3);
		$deckCards[] = $deckCard;
		
		$deckCard  = new DeckCard();
		$card4 = new Card();
		$card4->setCardName("Aatchik, Emerald Radian");
		$card4->setIdScryfall("e789df76-d658-47a4-9efb-74da6bd8821c");
		$deckCard->setCard($card4);
		$deckCards[] = $deckCard;
		
		$deckCard  = new DeckCard();
		$card5 = new Card();
		$card5->setCardName("Isshin 2");
		$card5->setIdScryfall("1bf8b008-3a7c-4b6d-8c18-263a576f0d64");
		$deckCard->setCard($card5);
		$deckCards[] = $deckCard;
		
		$deckCard  = new DeckCard();
		$card6 = new Card();
		$card6->setCardName("counter spell");
		$card6->setIdScryfall("5d93b770-dc46-46ad-aefe-282dac8cc246");
		$deckCard->setCard($card6);
		$deckCards[] = $deckCard;

		$deck->setDeckCards($deckCards);
		
        return $this->render('deckManagement/deck.html.twig', ["deck" => $deck]);
    }
}
