<?php

namespace App\Controller;

use App\Service\DeckBuilderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class DeckApiController extends AbstractController
{
    private $deckBuilderService;
    
    public function __construct(DeckBuilderService $deckBuilderService)
    {
        $this->deckBuilderService = $deckBuilderService;
    }
    
    #[Route('/deck/add-card', name: 'add_card_to_deck', methods: ['POST'])]
    public function addCardToDeck(Request $request): JsonResponse
    {
        // Verificar que el usuario está autenticado
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        // Obtener los datos enviados por AJAX
        $data = json_decode($request->getContent(), true);
        
        // Validar datos necesarios
        if (!isset($data['deckId']) || !isset($data['cardId']) || !isset($data['quantity'])) {
            return $this->json([
                'success' => false,
                'message' => 'Faltan parámetros requeridos: deckId, cardId, quantity'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $deckId = $data['deckId'];
        $cardId = $data['cardId'];
        $quantity = (int)$data['quantity'];
        $isSideboard = $data['isSideboard'] ?? false;
        
        try {
            // Llamar al servicio para añadir la carta
            $result = $this->deckBuilderService->addCardToDeck(
                $user,
                $deckId,
                $cardId,
                $quantity,
                $isSideboard
            );
            
            // Devolver respuesta con datos actualizados
            return $this->json([
                'success' => true,
                'message' => 'Carta añadida correctamente',
                'deck' => [
                    'id' => $result['deck']->getId(),
                    'name' => $result['deck']->getName(),
                    'cardCount' => $result['cardCount'],
                    'mainboardCount' => $result['mainboardCount'],
                    'sideboardCount' => $result['sideboardCount'],
                    'deckValue' => $result['deckValue']
                ],
                'card' => [
                    'id' => $result['card']->getId(),
                    'name' => $result['card']->getName(),
                    'quantity' => $result['quantity'],
                    'isSideboard' => $result['isSideboard']
                ]
            ]);

        } catch (\App\Exception\DeckNotFoundException $e) {
            return $this->json(['success' => false, 'message' => 'Mazo no encontrado'], 404);
            
        } catch (\App\Exception\CardNotFoundException $e) {
            return $this->json(['success' => false, 'message' => 'Carta no encontrada'], 404);
            
        } catch (\App\Exception\InvalidCardQuantityException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false, 
                'message' => 'Error interno del servidor al añadir la carta al mazo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/deck/remove-card', name: 'remove_card_from_deck', methods: ['POST'])]
    public function removeCardFromDeck(Request $request): JsonResponse
    {
        // Similar al método anterior pero para eliminar cartas
        // ...
        return $this->json([
            'success' => true,
        ]);
    }
    
    #[Route('/deck/{id}/cards', name: 'get_deck_cards', methods: ['GET'])]
    public function getDeckCards(int $id): JsonResponse
    {
        // Método para obtener todas las cartas de un mazo
        // ...
        return $this->json([
            'success' => true,
        ]);
    }
}
