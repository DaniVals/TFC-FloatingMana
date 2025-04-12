<?php
namespace App\Controller;

use App\Service\ScryfallApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CollectionController
extends AbstractController
{
    // This controller will handle the collection management
    // It will include methods to add, remove, and view cards in the collection
    // It will also include methods to manage the collection's metadata

	// debug controller to check how to make a collection list
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
}
