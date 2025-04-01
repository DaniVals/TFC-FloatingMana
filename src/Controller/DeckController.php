<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeckController extends AbstractController {
    public function index()
    {
        return $this->render('deck/index.html.twig');
    }
}
