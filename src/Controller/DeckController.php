<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Deck;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/deck', name: 'deck_')]
class DeckController extends AbstractController {
    private DeckRepository $deckRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(DeckRepository $deckRepository, EntityManagerInterface $entityManager) {
	$this->deckRepository = $deckRepository;
	$this->entityManager = $entityManager;
    }

    #[Route('/', name: 'index')]
    public function index() {
	return $this->render('deckManagement/index.html.twig');
    }

    #[Route('/{id}', name: 'show')]
    public function show(int $id) {
	$deck = $this->deckRepository->findOneById($id);
	return $this->render('deckManagement/deck.html.twig', [
	    'deck' => $deck,
	]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Deck $deck) {
	return $this->render('deckManagement/edit.html.twig', [
	    'deck' => $deck,
	]);
    }

}
