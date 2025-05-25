<?php

namespace App\Tests\Controller;

use App\Controller\CardController;
use App\Service\ScryfallApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CardControllerTest extends KernelTestCase
{
    private $controller;
    private $scryfallApiServiceMock;

    protected function setUp(): void
    {
        // Creamos un mock del servicio ScryfallApiService
        $this->scryfallApiServiceMock = $this->createMock(ScryfallApiService::class);
            
        // Type assertions
        /** @var ScryfallApiService $scryfallApiServiceMock **/
        $scryfallApiServiceMock = $this->scryfallApiServiceMock;

        // Instanciamos el controlador con el mock del servicio
        $this->controller = new CardController($scryfallApiServiceMock);
        
        // Configuramos el método render() del controlador para simular su comportamiento
        $this->controller = $this->getMockBuilder(CardController::class)
            ->setConstructorArgs([$this->scryfallApiServiceMock])
            ->onlyMethods(['render'])
            ->getMock();
    }

    public function testIndex()
    {
        // Datos de ejemplo de una carta aleatoria
        $randomCardData = [
            'id' => 'abc123',
            'name' => 'Carta Aleatoria',
            'mana_cost' => '{2}{R}',
            'type_line' => 'Criatura — Humano Guerrero',
            'oracle_text' => 'Algún texto de reglas...',
            'image_uris' => ['normal' => 'url/imagen.jpg']
        ];

        // Configuramos lo que debe devolver el mock del servicio
        $this->scryfallApiServiceMock
            ->expects($this->once())
            ->method('getRandomCard')
            ->willReturn($randomCardData);

        // Configuramos lo que debe devolver el método render
        $expectedResponse = new Response('contenido HTML simulado');
        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'cardManagement/viewCard.html.twig',
                ['card' => $randomCardData]
            )
            ->willReturn($expectedResponse);

        // Ejecutamos el método a probar
        $response = $this->controller->index();

        // Verificamos que la respuesta sea la esperada
        $this->assertSame($expectedResponse, $response);
    }

    public function testViewCard()
    {
        // ID de ejemplo
        $cardId = 'abc123';

        // Datos de ejemplo de una carta específica
        $cardData = [
            'id' => $cardId,
            'name' => 'Carta Específica',
            'mana_cost' => '{1}{W}',
            'type_line' => 'Encantamiento',
            'oracle_text' => 'Algún texto de reglas...',
            'image_uris' => ['normal' => 'url/imagen.jpg']
        ];

        // Configuramos lo que debe devolver el mock del servicio
        $this->scryfallApiServiceMock
            ->expects($this->once())
            ->method('getCardById')
            ->with($cardId)
            ->willReturn($cardData);

        // Configuramos lo que debe devolver el método render
        $expectedResponse = new Response('contenido HTML simulado');
        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'cardManagement/viewCard.html.twig',
                ['card' => $cardData]
            )
            ->willReturn($expectedResponse);

        // Ejecutamos el método a probar
        $response = $this->controller->viewCard($cardId);

        // Verificamos que la respuesta sea la esperada
        $this->assertSame($expectedResponse, $response);
    }

    public function testViewCardWithEmptyId()
    {
        // ID vacío
        $cardId = '';

        // Configuramos lo que debe devolver el método render
        $expectedResponse = new Response('contenido HTML simulado');
        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'cardManagement/searchCard.html.twig',
                [
                    'cards' => [],
                    'message' => 'Por favor, introduce un id de carta para buscar.',
                    Response::HTTP_BAD_REQUEST
                ]
            )
            ->willReturn($expectedResponse);

        // Ejecutamos el método a probar
        $response = $this->controller->viewCard($cardId);

        // Verificamos que la respuesta sea la esperada
        $this->assertSame($expectedResponse, $response);
    }

    public function testBuscar()
    {
        // Nombre de ejemplo
        $cardName = 'Dragón';

        // Datos de ejemplo de búsqueda de cartas
        $cardsData = [
            [
                'id' => 'xyz789',
                'name' => 'Dragón Carmesí',
                'mana_cost' => '{4}{R}{R}',
                'type_line' => 'Criatura — Dragón',
                'oracle_text' => 'Vuela',
                'image_uris' => ['normal' => 'url/dragon.jpg']
            ],
            [
                'id' => 'def456',
                'name' => 'Señor de Dragones',
                'mana_cost' => '{3}{B}',
                'type_line' => 'Criatura — Humano Brujo',
                'oracle_text' => 'Las criaturas Dragón que controlas obtienen +2/+0',
                'image_uris' => ['normal' => 'url/senor.jpg']
            ]
        ];

        // Configuramos lo que debe devolver el mock del servicio
        $this->scryfallApiServiceMock
            ->expects($this->once())
            ->method('searchCards')
            ->with($cardName)
            ->willReturn($cardsData);

        // Configuramos lo que debe devolver el método render
        $expectedResponse = new Response('contenido HTML simulado');
        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'cardManagement/searchCard.html.twig',
                ['cards' => $cardsData]
            )
            ->willReturn($expectedResponse);

        // Ejecutamos el método a probar
        $response = $this->controller->buscar($cardName);

        // Verificamos que la respuesta sea la esperada
        $this->assertSame($expectedResponse, $response);
    }

    public function testBuscarWithEmptyName()
    {
        // Nombre vacío
        $cardName = '';

        // Configuramos lo que debe devolver el método render
        $expectedResponse = new Response('contenido HTML simulado');
        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'cardManagement/searchCard.html.twig',
                [
                    'cards' => [],
                    'message' => 'Por favor, introduce un nombre de carta para buscar.',
                    Response::HTTP_BAD_REQUEST
                ]
            )
            ->willReturn($expectedResponse);

        // Ejecutamos el método a probar
        $response = $this->controller->buscar($cardName);

        // Verificamos que la respuesta sea la esperada
        $this->assertSame($expectedResponse, $response);
    }
}
