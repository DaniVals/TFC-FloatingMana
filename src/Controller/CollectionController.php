<?php
namespace App\Controller;

use App\Service\ScryfallApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Deck;

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
		$deck->setDeckName('Default Name'); // Replace with your desired default values
		$deck->setType('Default Type'); // Replace with your desired default values

		$deckCards = [];
		$deckCards[] = ["id" => "4b4390f4-451f-4575-96e0-dc4dcb45ad8f", "name" => "nombre1"];
		$deckCards[] = ["id" => "047f196b-a9d1-4cd3-b665-3b304cc59767", "name" => "nombre2"];
		$deckCards[] = ["id" => "e789df76-d658-47a4-9efb-74da6bd8821c", "name" => "Aatchik, Emerald Radian"];
		$deckCards[] = ["id" => "a062a004-984e-4b62-960c-af7288f7a3e9", "name" => "Isshin 1"];
		$deckCards[] = ["id" => "1bf8b008-3a7c-4b6d-8c18-263a576f0d64", "name" => "Isshin 2"];
		$deckCards[] = ["id" => "5d93b770-dc46-46ad-aefe-282dac8cc246", "name" => "counter spell"];

        return $this->render('deckManagement/deck.html.twig', ["deck" => $deck, "deckCards" => $deckCards]);
    }
}
