<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Deck;
use App\Repository\DeckRepository;
use App\Service\DeckBuilderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



#[Route('/deck', name: 'deck_')]
class DeckController extends AbstractController {
    private DeckRepository $deckRepository;
    private DeckBuilderService $deckBuilderService;


    public function __construct(DeckRepository $deckRepository, DeckBuilderService $deckBuilderService) {
	$this->deckRepository = $deckRepository;
	$this->deckBuilderService = $deckBuilderService;
    }

    #[Route('/', name: 'index')]
    public function index() {
	return $this->render('deckManagement/index.html.twig');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id) {
	$deck = $this->deckRepository->findOneById($id);
	return $this->render('deckManagement/deck.html.twig', [
	    'deck' => $deck,
	]);
    }

    // Render de la pagina para la creación de mazos
    #[Route('/create', name: 'create_view', methods: ['GET'])]
    public function create_view() {
	return $this->render('deckManagement/create.html.twig');
    }

    // Recibe el nombre del mazo y el formato
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response {
	// Verificar que el usuario está autenticado
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
	$user = $this->getUser();

	$deckName = $request->request->get('_deckName');
	$deckFormat = $request->request->get('_deckFormat');

	try {
	    $deck = $this->deckBuilderService->createDeck($user, (string)$deckName, (string)$deckFormat);
	    $this->deckRepository->save($deck, true);

	    return $this->render('deckManagement/deck.html.twig', [
		'responseData' => [
		    'success' => true,
		    'message' => 'Mazo creado correctamente',
		    'status'  => Response::HTTP_OK,
		    'deck' => $deck
		]
	    ]);
	} catch (\Exception $e) {
	    return $this->render('deckManagement/create.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => $e->getMessage(),
		    'status'  => Response::HTTP_BAD_REQUEST
		]

	    ]);
	}
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(int $id): Response {
	// Verificar que el usuario está autenticado
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
	$user = $this->getUser();

	$deck = $this->deckRepository->findOneById($id);
	if (!$deck) {
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'Mazo no encontrado',
		    'status'  => Response::HTTP_NOT_FOUND
		]
	    ]);
	}

	if ($deck->getUser() !== $user) {
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'No tienes permiso para eliminar este mazo',
		    'status'  => Response::HTTP_FORBIDDEN
		]
	    ]);
	}

	try {
	    $this->deckRepository->remove($deck, true);
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => true,
		    'message' => 'Mazo eliminado correctamente',
		    'status'  => Response::HTTP_OK
		]
	    ]);

	} catch (\Exception $e) {
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'Error al eliminar el mazo: ' . $e->getMessage(),
		    'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
		]
	    ]);
	}
    }


    #[Route('/edit/{id}', name: 'edit_view', requirements: ['id' => '\d+'])]
    public function edit_view(int $id): Response {
	// Verificar que el usuario está autenticado
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
	$user = $this->getUser();
	$deck = $this->deckRepository->findOneById($id);
	if (!$deck) {
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'Mazo no encontrado',
		    'status'  => Response::HTTP_NOT_FOUND

		]
	    ]);
	}
	if ($deck->getUser() !== $user) {
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'No tienes permiso para editar este mazo',
		    'status'  => Response::HTTP_FORBIDDEN
		]
	    ]);
	}

	return $this->render('deckManagement/edit.html.twig', [
	    'deck' => $deck,
	    'responseData' => [
		'success' => true,
		'message' => 'Mazo encontrado',
		'status'  => Response::HTTP_OK
	    ]
	]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, int $id): Response {

	// Verificar que el usuario está autenticado
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
	$user = $this->getUser();
	$deck = $this->deckRepository->findOneById($id);
	if (!$deck) {
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'Mazo no encontrado',
		    'status'  => Response::HTTP_NOT_FOUND

		]
	    ]);
	}
	if ($deck->getUser() !== $user) {
	    return $this->render('deckManagement/index.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'No tienes permiso para editar este mazo',
		    'status'  => Response::HTTP_FORBIDDEN
		]
	    ]);
	}

	$deckName = $request->request->get('_deckName');
	$deckFormat = $request->request->get('_deckFormat');
	try {
	    $deck->setDeckName($deckName);
	    $deck->setType($deckFormat);
	    $this->deckRepository->save($deck, true);

	    return $this->render('deckManagement/deck.html.twig', [
		'responseData' => [
		    'success' => true,
		    'message' => 'Mazo editado correctamente',
		    'status'  => Response::HTTP_OK,
		    'deck' => $deck

		]
	    ]);
	} catch (\Exception $e) {
	    return $this->render('deckManagement/edit.html.twig', [
		'responseData' => [
		    'success' => false,
		    'message' => 'Error al editar el mazo: ' . $e->getMessage(),
		    'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
		]
	    ]);
	}
    }
}
