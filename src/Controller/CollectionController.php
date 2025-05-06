<?php

namespace App\Controller;

use App\Service\CollectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/collection", name="collection_")
 */
class CollectionController extends AbstractController
{
    private $collectionService;
    private $serializer;
    private $security;

    public function __construct(
        CollectionService $collectionService,
        SerializerInterface $serializer,
        // Security $security
    ) {
        $this->collectionService = $collectionService;
        $this->serializer = $serializer;
        // $this->security = $security;
    }

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        try {
            $collection = $this->collectionService->getUserCollection();
            return $this->json([
                'status' => 'success',
                'data' => $collection
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/stats", name="stats", methods={"GET"})
     */
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

    /**
     * @Route("/search", name="search", methods={"GET"})
     */
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

    /**
     * @Route("/card/{cardId}", name="card_detail", methods={"GET"})
     */
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

    /**
     * @Route("/card", name="add_card", methods={"POST"})
     */
    public function addCard(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['card_id'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'ID de carta no proporcionado'
            ], Response::HTTP_BAD_REQUEST);
        }

        $quantity = $data['quantity'] ?? 1;

        try {
            $collectionItem = $this->collectionService->addCardToCollection($data['card_id'], $quantity);
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

    /**
     * @Route("/card/{cardId}", name="update_card", methods={"PUT"})
     */
    public function updateCard(int $cardId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['quantity'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Cantidad no proporcionada'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $collectionItem = $this->collectionService->updateCardQuantity($cardId, $data['quantity']);
            return $this->json([
                'status' => 'success',
                'message' => 'Cantidad actualizada',
                'data' => $collectionItem
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/card/{cardId}", name="remove_card", methods={"DELETE"})
     */
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

    /**
     * @Route("/import", name="import", methods={"POST"})
     */
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

    /**
     * @Route("/view", name="view", methods={"GET"})
     */
    public function viewCollection(): Response
    {
        try {
            $collection = $this->collectionService->getUserCollection();
            $stats = $this->collectionService->getCollectionStats();
            
            return $this->render('collection/index.html.twig', [
                'collection' => $collection,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('dashboard_index');
        }
    }

    /**
     * @Route("/export", name="export", methods={"GET"})
     */
    public function exportCollection(): Response
    {
        try {
            $collection = $this->collectionService->getUserCollection();
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
}
