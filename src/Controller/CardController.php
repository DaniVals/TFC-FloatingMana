<?php
namespace App\Controller;

use App\Service\ScryfallApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardController extends AbstractController {
    private ScryfallApiService $scryfallApiService;

    public function __construct(ScryfallApiService $scryfallApiService)
    {
        $this->scryfallApiService = $scryfallApiService;
    }

    #[Route('/carta', name: 'random_card')]   
    public function index(): Response {
        $cardInfo = $this->scryfallApiService->getRandomCard();
        return $this->render('cardManagement/viewCard.html.twig', ['card' => $cardInfo]);
    }

    #[Route('/carta/{id}', name: 'view_card')]
    public function viewCard(string $id): Response {
        $cardInfo = $this->scryfallApiService->getCardById($id);
        return $this->render('cardManagement/viewCard.html.twig', ['card' => $cardInfo]);
    }

    #[Route('/buscar/{nombre}', name: 'search_card')]
    public function buscar(string $nombre): Response {
        $cards = $this->scryfallApiService->searchCards($nombre);
        return $this->render('cardManagement/searchCard.html.twig', ['cards' => $cards]);
    }
}
