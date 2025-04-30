<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Deck;

class DeckController extends AbstractController {

    #[Route('/testing/vals/deck', name: 'testing_vals_deck')]
    public function vals_deck()
    {
		$deck = new Deck();
		$deck->setDeckName('Default Name'); // Replace with your desired default values
		$deck->setType('Default Type'); // Replace with your desired default values

        return $this->render('deck/deck.html.twig', ["deck" => $deck]);
    }
}
