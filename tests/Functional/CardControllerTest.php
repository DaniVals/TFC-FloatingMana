<?php
namespace App\Tests\Functional\Controller;

use App\Service\ScryfallApiService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CardControllerTest extends WebTestCase
{
    public function testRandomCard()
    {
        $client = static::createClient();
        
        // Mock para ScryfallApiService
        $randomCardData = [
            'id' => 'random-id-123',
            'name' => 'Black Lotus',
            'set_name' => 'Alpha',
            'rarity' => 'rare',
            'image_uris' => [
                'normal' => 'https://example.com/black_lotus.jpg'
            ]
        ];
        
        $scryfallApiServiceMock = $this->createMock(ScryfallApiService::class);
        $scryfallApiServiceMock->expects($this->once())
            ->method('getRandomCard')
            ->willReturn($randomCardData);
        
        // Reemplazar el servicio real con el mock
        $client->getContainer()->set('App\Service\ScryfallApiService', $scryfallApiServiceMock);
        
        // Hacer la solicitud
        $client->request('GET', '/carta');
        
        // Verificar respuesta
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1, h2', 'Black Lotus');
        $this->assertSelectorExists('img[src="https://example.com/black_lotus.jpg"]');
    }
    
    public function testViewCardById()
    {
        $client = static::createClient();
        
        $cardId = 'specific-card-456';
        $cardData = [
            'id' => $cardId,
            'name' => 'Sol Ring',
            'set_name' => 'Commander',
            'rarity' => 'uncommon',
            'image_uris' => [
                'normal' => 'https://example.com/sol_ring.jpg'
            ]
        ];
        
        $scryfallApiServiceMock = $this->createMock(ScryfallApiService::class);
        $scryfallApiServiceMock->expects($this->once())
            ->method('getCardById')
            ->with($cardId)
            ->willReturn($cardData);
        
        $client->getContainer()->set('App\Service\ScryfallApiService', $scryfallApiServiceMock);
        
        // Hacer la solicitud
        $client->request('GET', '/carta/' . $cardId);
        
        // Verificar respuesta
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1, h2', 'Sol Ring');
        $this->assertSelectorExists('img[src="https://example.com/sol_ring.jpg"]');
    }
    
    public function testSearchCards()
    {
        $client = static::createClient();
        
        $searchTerm = 'lightning';
        $searchResults = [
            [
                'id' => 'card-id-1',
                'name' => 'Lightning Bolt',
                'set_name' => 'Alpha',
                'rarity' => 'common',
                'image_uris' => [
                    'normal' => 'https://example.com/lightning_bolt.jpg'
                ]
            ],
            [
                'id' => 'card-id-2',
                'name' => 'Lightning Helix',
                'set_name' => 'Ravnica',
                'rarity' => 'uncommon',
                'image_uris' => [
                    'normal' => 'https://example.com/lightning_helix.jpg'
                ]
            ]
        ];
        
        $scryfallApiServiceMock = $this->createMock(ScryfallApiService::class);
        $scryfallApiServiceMock->expects($this->once())
            ->method('searchCards')
            ->with($searchTerm)
            ->willReturn($searchResults);
        
        $client->getContainer()->set('App\Service\ScryfallApiService', $scryfallApiServiceMock);
        
        // Hacer la solicitud
        $client->request('GET', '/buscar/' . $searchTerm);
        
        // Verificar respuesta
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1, h2, .card-title', 'Lightning Bolt');
        $this->assertSelectorTextContains('h1, h2, .card-title', 'Lightning Helix');
        $this->assertSelectorExists('img[src="https://example.com/lightning_bolt.jpg"]');
        $this->assertSelectorExists('img[src="https://example.com/lightning_helix.jpg"]');
    }
    
    public function testViewCardWithNonExistentId()
    {
        $client = static::createClient();
        
        $nonExistentId = 'non-existent-card';
        
        // Mock para simular una tarjeta no encontrada
        $scryfallApiServiceMock = $this->createMock(ScryfallApiService::class);
        $scryfallApiServiceMock->expects($this->once())
            ->method('getCardById')
            ->with($nonExistentId)
            ->willReturn(null);
        
        $client->getContainer()->set('App\Service\ScryfallApiService', $scryfallApiServiceMock);
        
        // Hacer la solicitud
        $client->request('GET', '/carta/' . $nonExistentId);
        
        // Verificar respuesta (dependiendo de cómo se manejen los casos de tarjeta no encontrada)
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-warning, .error-message', 'no encontrada');
    }
    
    public function testSearchWithNoResults()
    {
        $client = static::createClient();
        
        $searchTerm = 'cartainexistente12345';
        
        // Mock para simular búsqueda sin resultados
        $scryfallApiServiceMock = $this->createMock(ScryfallApiService::class);
        $scryfallApiServiceMock->expects($this->once())
            ->method('searchCards')
            ->with($searchTerm)
            ->willReturn([]);
        
        $client->getContainer()->set('App\Service\ScryfallApiService', $scryfallApiServiceMock);
        
        // Hacer la solicitud
        $client->request('GET', '/buscar/' . $searchTerm);
        
        // Verificar respuesta
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.no-results, .alert-info', 'No se encontraron resultados');
    }
}
