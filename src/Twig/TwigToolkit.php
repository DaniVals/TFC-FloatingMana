<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\State;

class TwigToolkit extends AbstractExtension
{
	// añadir aqui todas las funciones con su nombre a usar en twig y el nombre de la funcion
    public function getFunctions(): array
    {
        return [
			new TwigFunction('get_card_state_list', [$this, 'getCardStateList']),
        ];
    }

    public function getCardStateList()
    {
		$stateList = [];
		$state = new State();
		$state->setIdState(-1);
		$state->setStateName("como nueva soncio");
		$stateList[] = $state;
        return $stateList;
    }
}