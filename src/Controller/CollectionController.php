<?php

namespace App\Controller;

use App\Service\CollectionService;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/collection', name: 'collection_')]
class CollectionController extends AbstractController
{
    private $collectionService;
    private $userRepository;

    public function __construct(
        CollectionService $collectionService,
        UserRepository $userRepository
    ) {
        $this->collectionService = $collectionService;
        $this->userRepository = $userRepository;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception('Usuario no autenticado');
            }
            $collectionArray = $this->collectionService->getUserCollection($user);

            return $this->render('collectionManagement/collection.html.twig', [
                'title' => 'Mi colección',
                'description' => 'Aquí puedes ver y gestionar tu colección de cartas.',
                'status' => 'success',
                'collection' => $collectionArray
        ]);
        } catch (\Exception $e) {
            return $this->render('collectionManagement/collection.html.twig', [
                'title' => 'Mi colección',
                'description' => 'Aquí puedes ver y gestionar tu colección de cartas.',
                'status' => 'error',
                'message' => $e->getMessage(),
                'collection' => [] // Provide an empty array as fallback
            ]);
        }
    }

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->collectionService->getCollectionStats();
            return $this->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q');
        if (!$query) {
            return $this->json([
                'status' => 'error',
                'message' => 'Parámetro de búsqueda no proporcionado'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $results = $this->collectionService->searchInCollection($query);
            return $this->json([
                'status' => 'success',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/card/{cardId}', name: 'card_detail', methods: ['GET'])]
    public function getCard(int $cardId): JsonResponse
    {
        try {
            $collectionItem = $this->collectionService->getCardFromCollection($cardId);
            if (!$collectionItem) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Carta no encontrada en tu colección'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'status' => 'success',
                'data' => $collectionItem
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/card', name: 'add_card', methods: ['POST'])]
    public function addCard(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar datos necesarios
        if (!isset($data['card_id']) || !isset($data['quantity']) || !isset($data['isFoil']) || !isset($data['state']) || !isset($data['purchasePrice'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Parámetros requeridos no proporcionados.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $cardId = $data['card_id'];
        $quantity = (int)$data['quantity'] ?? 1;
        $isFoil = (int)$data['isFoil'] ?? 0;
        $state = $data['state'] ?? 2;
        $price = (int)$data['purchasePrice'] ?? 0.0;


        try {
            $collectionItem = $this->collectionService->addCardToCollection($cardId, $quantity, $price, $isFoil, $state);
            return $this->json([
                'status' => 'success',
                'message' => 'Carta añadida a la colección',
                'data' => $collectionItem
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/update', name: 'update', methods: ['POST'])]
    public function updateCollection(Request $request): Response
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

            // Verificar que existe el array changed_card
            if (!isset($data['changed_card']) || !is_array($data['changed_card'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'El campo "changed_card" es requerido y debe ser un array'
                ], Response::HTTP_BAD_REQUEST);
            }

            $changedCards = $data['changed_card'];

            // Verificar que no esté vacío
            if (empty($changedCards)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'No se encontraron cartas para actualizar'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Procesar las actualizaciones usando el servicio
            $result = $this->collectionService->updateUserCollection($user, $changedCards);

            // Preparar respuesta basada en el resultado
            if ($result['success_count'] > 0) {
                $message = "Se actualizaron {$result['success_count']} cartas correctamente";
                
                if (!empty($result['errors'])) {
                    $message .= ", pero se encontraron " . count($result['errors']) . " errores";
                }
                
                // Recargar la web con un menaje de éxito
                return $this->json([
                    'status' => 'success',
                    'message' => $message,
                    'data' => [
                        'updated_count' => $result['success_count'],
                        'errors' => $result['errors']
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

    #[Route('/card/{cardId}', name: 'remove_card', methods: ['DELETE'])]
    public function removeCard(int $cardId): JsonResponse
    {
        try {
            $this->collectionService->removeCardFromCollection($cardId);
            return $this->json([
                'status' => 'success',
                'message' => 'Carta eliminada de la colección'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/import', name: 'import', methods: ['POST'])]
    public function importCollection(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['card_list']) || empty($data['card_list'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Lista de cartas no proporcionada'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $results = $this->collectionService->importCardList($data['card_list']);

            return $this->json([
                'status' => 'success',
                'message' => 'Importación completada',
                'data' => [
                    'imported' => count($results['success']),
                    'failed' => count($results['errors']),
                    'details' => $results
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/view', name: 'view', methods: ['GET'])]
    public function viewCollection(): Response
    {
        try {
            $collection = $this->collectionService->getUserCollection($this->getUser());
            $stats = $this->collectionService->getCollectionStats();

            return $this->render('collectionManagement/collection.html.twig', [
                'title' => 'Mi colección',
                'description' => 'Aquí puedes ver y gestionar tu colección de cartas.',
                'collection' => $collection,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('dashboard_index');
        }
    }

    #[Route('/export', name: 'export', methods: ['GET'])]
    public function exportCollection(): Response
    {
        try {
            $collection = $this->collectionService->getUserCollection($this->getUser());
            $exportData = '';

            foreach ($collection as $item) {
                $exportData .= $item->getQuantity() . 'x ' . $item->getCard()->getName() . "\n";
            }

            $response = new Response($exportData);
            $response->headers->set('Content-Type', 'text/plain');
            $response->headers->set('Content-Disposition', 'attachment; filename="collection_export.txt"');

            return $response;
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/fetch_most_valuable', name: 'fetch_most_valuable', methods: ['GET', 'POST'])]
    public function fetchMostValuableCards(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['limit']) || !is_int($data['limit'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Parámetro "limit" no proporcionado o inválido'
                ], Response::HTTP_BAD_REQUEST);
            }

            $limit = (int)$data['limit'];
            $user = $this->getUser();
            $cards = $this->collectionService->fetchMostValuableCards($user ,$limit);

            return $this->json([
                'status' => 'success',
                'data' => $cards
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
