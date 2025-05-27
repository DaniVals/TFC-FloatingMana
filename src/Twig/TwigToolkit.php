<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\State;
use Doctrine\ORM\EntityManagerInterface;

class TwigToolkit extends AbstractExtension
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
	$this->entityManager = $entityManager;
    }

	// añadir aqui todas las funciones con su nombre a usar en twig y el nombre de la funcion
    public function getFunctions(): array
    {
        return [
			new TwigFunction('get_card_state_list', [$this, 'getCardStateList']),
        ];
    }

    public function getCardStateList()
    {
	// Devolver un array con todos los estados almacenados en la base de datos
	// Order by idState ASC
	$state = $this->entityManager->getRepository(State::class)->findBy([], ['idState' => 'ASC']);

	return $state;
    }
}
