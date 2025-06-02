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
        return $this->redirectToRoute('view_card', ['id' => $cardInfo['id']]);
    }

    #[Route('/carta/{id}', name: 'view_card')]
    public function viewCard(string $id): Response {
        if (empty($id)) {
            return $this->render('cardManagement/searchCard.html.twig', [
                'cards' => [],
                'message' => 'Por favor, introduce un id de carta para buscar.',
                Response::HTTP_BAD_REQUEST
            ]);
        }

        try {
            $cardInfo = $this->scryfallApiService->getCardById($id);
            return $this->render('cardManagement/viewCard.html.twig', ['card' => $cardInfo]);
        } catch (\Exception $e) {
            return $this->render('cardManagement/viewCard.html.twig', [
                'error' => 'Error al obtener la carta: ' . $e->getMessage(),
                'card' => [],
                Response::HTTP_NOT_FOUND
            ]);
        }
    }

    #[Route('/buscar', name: 'search_card_form')]
    public function buscarForm(): Response {
        return $this->render('cardManagement/searchCard.html.twig');
    }

    #[Route('/buscar/{nombre}', name: 'search_card')]
    public function buscar(string $nombre): Response {
        // Gestionar busquedas vacías
        if (empty($nombre)) {
            return $this->render('cardManagement/searchCard.html.twig', [
                'cards' => [],
                'message' => 'Por favor, introduce un nombre de carta para buscar.',
                Response::HTTP_BAD_REQUEST
            ]);
        }
        try {
            $cards = $this->scryfallApiService->searchCards($nombre);
            return $this->render('cardManagement/searchCard.html.twig', [
                'cards' => $cards,
                'nombre' => $nombre
            ]);
        } catch (\Exception $e) {
            return $this->render('cardManagement/searchCard.html.twig', [
                'error' => 'Error al buscar cartas: ' . $e->getMessage(),
                'cards' => [],
                Response::HTTP_INTERNAL_SERVER_ERROR
            ]);
        }
    }
}
