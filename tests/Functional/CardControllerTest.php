<?php

namespace App\Tests\Functional\Controller;

use App\Controller\CardController;
use App\Service\ScryfallApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Psr\Container\ContainerInterface;

class CardControllerTest extends TestCase
{
    private $scryfallApiServiceMock;
    private Environment $twig;

    protected function setUp(): void
    {
        $this->scryfallApiServiceMock = $this->createMock(ScryfallApiService::class);

        // Simulamos el motor de plantillas Twig para que no haga falta render real
        $loader = new ArrayLoader([
            'cardManagement/viewCard.html.twig' => 'Card template: {{ card.name }}',
            'cardManagement/searchCard.html.twig' => 'Search template: {{ cards|length }} results',
        ]);
        $this->twig = new Environment($loader);
    }

    public function testIndexReturnsRandomCardView(): void
    {
        $cardData = ['name' => 'Black Lotus'];
        $this->scryfallApiServiceMock
            ->method('getRandomCard')
            ->willReturn($cardData);

        $controller = new CardController($this->scryfallApiServiceMock);
        $controller->setContainer($this->createMockContainer());

        $response = $controller->index();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Card template: Black Lotus', $response->getContent());
    }

    public function testViewCardReturnsSpecificCard(): void
    {
        $cardId = '12345';
        $cardData = ['name' => 'Lightning Bolt'];
        $this->scryfallApiServiceMock
            ->method('getCardById')
            ->with($cardId)
            ->willReturn($cardData);

        $controller = new CardController($this->scryfallApiServiceMock);
        $controller->setContainer($this->createMockContainer());

        $response = $controller->viewCard($cardId);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Card template: Lightning Bolt', $response->getContent());
    }

    public function testBuscarReturnsMatchingCards(): void
    {
        $searchTerm = 'Bolt';
        $cardList = [
            ['name' => 'Lightning Bolt'],
            ['name' => 'Forked Bolt']
        ];
        $this->scryfallApiServiceMock
            ->method('searchCards')
            ->with($searchTerm)
            ->willReturn($cardList);

        $controller = new CardController($this->scryfallApiServiceMock);
        $controller->setContainer($this->createMockContainer());

        $response = $controller->buscar($searchTerm);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Search template: 2 results', $response->getContent());
    }

    private function createMockContainer() : ContainerInterface
    {
        /** @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject */
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($this->twig);
        return $container;
    }
}

