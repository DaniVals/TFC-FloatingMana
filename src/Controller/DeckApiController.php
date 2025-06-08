<?php

namespace App\Controller;

use App\Repository\DeckRepository;
use App\Service\DeckBuilderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class DeckApiController extends AbstractController
{
    private $deckBuilderService;
    private $deckRepository;
    
    public function __construct(DeckBuilderService $deckBuilderService, DeckRepository $deckRepository)
    {
        $this->deckBuilderService = $deckBuilderService;
        $this->deckRepository = $deckRepository;
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
        $cardName = $data['cardName'] ?? $data['cardId'];
        $quantity = (int)$data['quantity'];
        
        try {
            // Llamar al servicio para añadir la carta
            $result = $this->deckBuilderService->addCardToDeck(
                $user,
                $deckId,
                $cardName,
                $cardId,
                $quantity,
            );
            
            // Devolver respuesta con datos actualizados
            return $this->json([
                'success' => true,
                'message' => 'Carta añadida correctamente',
                'deck' => [
                    'id' => $result['deck']->getIdDeck(),
                    'name' => $result['deck']->getDeckName(),
                    // 'cardCount' => $result['cardCount'],
                    // 'mainboardCount' => $result['mainboardCount'],
                    'deckValue' => $result['deckValue']
                ],
                'card' => [
                    'id' => $result['card']->getIdCard(),
                    'name' => $result['card']->getCardName(),
                    'quantity' => $result['quantity'],
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

        #[Route('/deck/update', name: 'deck_cards_update', methods: ['POST'])]
    public function updateDeck(Request $request): JsonResponse
    {
        try {
            // Obtener el usuario autenticado
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Decodificar el JSON del request
            $data = json_decode($request->getContent(), true);

            // Validar estructura del JSON
            if (!is_array($data)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Formato JSON inválido'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Verificar que existe el array changed_cards
            if (!isset($data['changed_card']) || !is_array($data['changed_card'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'El campo "changed_cards" es requerido y debe ser un array'
                ], Response::HTTP_BAD_REQUEST);
            }

            $changedCards = $data['changed_card'];

            // Verificar que el mazo existe y pertenece al usuario
            if (!isset($data['deck_id']) || !is_numeric($data['deck_id'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'El campo "deck" es requerido y debe ser un número'
                ], Response::HTTP_BAD_REQUEST);
            }
            $deck = $this->deckRepository->findOneByIdAndUser((int)$data['deck_id'], $user);
            if (!$deck) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Mazo no encontrado o no tienes permisos para modificarlo'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar que no esté vacío
            if (empty($changedCards)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'No se encontraron cartas para actualizar'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Procesar las actualizaciones usando el servicio
            $result = $this->deckBuilderService->updateDeckCards($user, $deck, $changedCards);

            // Preparar respuesta basada en el resultado
            if ($result['success_count'] > 0) {
                $message = "Se actualizaron {$result['success_count']} cartas correctamente";
                
                if (!empty($result['errors'])) {
                    $message .= ", pero se encontraron " . count($result['errors']) . " errores";
                }
                
                return $this->json([
                    'status' => 'success',
                    'message' => $message,
                    'data' => [
                        'updated_count' => $result['success_count'],
                        'errors' => $result['errors'],
                        'deck_value' => $result['deck_value'] ?? 0,
                        'total_cards' => $result['total_cards'] ?? 0
                    ]
                ]);
                
            } else {
                return $this->json([
                    'status' => 'error',
                    'message' => 'No se pudo actualizar ninguna carta',
                    'data' => [
                        'errors' => $result['errors']
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
