<?php

namespace App\Tests\Controller;

use App\Controller\CollectionController;
use App\Entity\User;
use App\Service\CollectionService;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CollectionControllerTest extends TestCase
{
    private $collectionService;
    private $userRepository;
    private $controller;
    private $user;

    protected function setUp(): void
    {
        $this->collectionService = $this->createMock(CollectionService::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->user = $this->createMock(User::class);

        // Create a partial mock for the controller
        $this->controller = $this->getMockBuilder(CollectionController::class)
            ->setConstructorArgs([$this->collectionService, $this->userRepository])
            ->onlyMethods(['getUser', 'render', 'json'])
            ->getMock();
            
        // Configure mock methods
        $this->controller->method('getUser')->willReturn($this->user);
    }
    
    public function testIndex()
    {
        // Arrange
        $collectionArray = ['card1', 'card2'];
        $this->collectionService->expects($this->once())
            ->method('getUserCollection')
            ->with($this->user)
            ->willReturn($collectionArray);
        
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'collectionManagement/collection.html.twig',
                [
                    'title' => 'Mi colección',
                    'description' => 'Aquí puedes ver y gestionar tu colección de cartas.',
                    'status' => 'success',
                    'collection' => $collectionArray
                ]
            )
            ->willReturn(new Response());
        
        // Act
        $response = $this->controller->index();
        
        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }
    
    public function testGetStats()
    {
        // Arrange
        $stats = ['total' => 100, 'unique' => 50];
        $this->collectionService->expects($this->once())
            ->method('getCollectionStats')
            ->willReturn($stats);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'success',
                'data' => $stats
            ])
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->getStats();
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testGetStatsWithException()
    {
        // Arrange
        $errorMessage = 'Test error';
        $this->collectionService->expects($this->once())
            ->method('getCollectionStats')
            ->willThrowException(new \Exception($errorMessage));
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'error',
                'message' => $errorMessage
            ], Response::HTTP_BAD_REQUEST)
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->getStats();
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testSearch()
    {
        // Arrange
        $query = 'test';
        $results = ['card1', 'card2'];
        
        $request = new Request();
        $request->query->set('q', $query);
        
        $this->collectionService->expects($this->once())
            ->method('searchInCollection')
            ->with($query)
            ->willReturn($results);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'success',
                'data' => $results
            ])
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->search($request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testSearchWithEmptyQuery()
    {
        // Arrange
        $request = new Request();
        // Don't set a 'q' parameter
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'error',
                'message' => 'Parámetro de búsqueda no proporcionado'
            ], Response::HTTP_BAD_REQUEST)
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->search($request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testGetCard()
    {
        // Arrange
        $cardId = 1;
        $collectionItem = $this->createMock(\App\Entity\Collection::class);
        
        $this->collectionService->expects($this->once())
            ->method('getCardFromCollection')
            ->with($cardId)
            ->willReturn($collectionItem);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'success',
                'data' => $collectionItem
            ])
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->getCard($cardId);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testGetCardNotFound()
    {
        // Arrange
        $cardId = 1;
        
        $this->collectionService->expects($this->once())
            ->method('getCardFromCollection')
            ->with($cardId)
            ->willReturn(null);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'error',
                'message' => 'Carta no encontrada en tu colección'
            ], Response::HTTP_NOT_FOUND)
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->getCard($cardId);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testAddCard()
    {
        // Arrange
        $user = $this->createMock(User::class);
        $cardId = 1;
        $quantity = 2;
        $isFoil = false;
        $purchasePrice = 10.0;
        $collectionItem = $this->createMock(\App\Entity\Collection::class);
        
        $requestContent = json_encode([
            'user' => $user,
            'card_id' => $cardId,
            'quantity' => $quantity,
            'isFoil' => $isFoil,
            'purchase_price' => $purchasePrice
        ]);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn($requestContent);
        
        $this->collectionService->expects($this->once())
            ->method('addCardToCollection')
            ->with($cardId, $quantity, $isFoil, $purchasePrice)
            ->willReturn($collectionItem);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'success',
                'message' => 'Carta añadida a la colección',
                'data' => $collectionItem
            ], Response::HTTP_CREATED)
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->addCard($request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testAddCardWithoutCardId()
    {
        // Arrange
        $requestContent = json_encode(['quantity' => 2]);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn($requestContent);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'error',
                'message' => 'ID de carta no proporcionado'
            ], Response::HTTP_BAD_REQUEST)
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->addCard($request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testUpdateCard()
    {
        // Arrange
        $cardId = 1;
        $quantity = 3;
        $collectionItem = $this->createMock(\App\Entity\Collection::class);
        
        $requestContent = json_encode(['quantity' => $quantity]);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn($requestContent);
        
        $this->collectionService->expects($this->once())
            ->method('updateCardQuantity')
            ->with($cardId, $quantity)
            ->willReturn($collectionItem);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'success',
                'message' => 'Cantidad actualizada',
                'data' => $collectionItem
            ])
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->updateCard($cardId, $request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testUpdateCardWithoutQuantity()
    {
        // Arrange
        $cardId = 1;
        
        $requestContent = json_encode([]);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn($requestContent);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'error',
                'message' => 'Cantidad no proporcionada'
            ], Response::HTTP_BAD_REQUEST)
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->updateCard($cardId, $request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testRemoveCard()
    {
        // Arrange
        $cardId = 1;
        
        $this->collectionService->expects($this->once())
            ->method('removeCardFromCollection')
            ->with($cardId);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'success',
                'message' => 'Carta eliminada de la colección'
            ])
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->removeCard($cardId);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testImportCollection()
    {
        // Arrange
        $cardList = "card1\ncard2"; // String input, not array
        $results = [
            'success' => ['card1'],
            'errors' => []
        ];
        
        $requestContent = json_encode(['card_list' => $cardList]);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn($requestContent);
        
        $this->collectionService->expects($this->once())
            ->method('importCardList')
            ->with($cardList)
            ->willReturn($results);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'success',
                'message' => 'Importación completada',
                'data' => [
                    'imported' => 1,
                    'failed' => 0,
                    'details' => $results
                ]
            ])
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->importCollection($request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testImportCollectionWithoutCardList()
    {
        // Arrange
        $requestContent = json_encode([]);
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn($requestContent);
        
        $this->controller->expects($this->once())
            ->method('json')
            ->with([
                'status' => 'error',
                'message' => 'Lista de cartas no proporcionada'
            ], Response::HTTP_BAD_REQUEST)
            ->willReturn(new JsonResponse());
        
        // Act
        $response = $this->controller->importCollection($request);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
    
    public function testViewCollection()
    {
        // Arrange
        $collection = ['card1', 'card2'];
        $stats = ['total' => 100, 'unique' => 50];
        
        $this->collectionService->expects($this->once())
            ->method('getUserCollection')
            ->with($this->user) // Ensure we pass the user parameter
            ->willReturn($collection);
        
        $this->collectionService->expects($this->once())
            ->method('getCollectionStats')
            ->willReturn($stats);
        
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'collectionManagement/collection.html.twig',
                [
                    'title' => 'Mi colección',
                    'description' => 'Aquí puedes ver y gestionar tu colección de cartas.',
                    'collection' => $collection,
                    'stats' => $stats
                ]
            )
            ->willReturn(new Response());
        
        // Act
        $response = $this->controller->viewCollection();
        
        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }
}
