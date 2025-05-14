<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'state')]

class State
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer', name: 'idState')]
	private int $idState;

	#[ORM\Column(type: 'string', length: 50, name: 'stateName')]
	private string $stateName;

	// ----- Getters y Setters -----

	public function getIdState(): int
	{
		return $this->idState;
	}
	public function setIdState(int $idState): self
	{
		$this->idState = $idState;
		return $this;
	}

	public function getStateName(): string
	{
		return $this->stateName;
	}
	public function setStateName(string $stateName): self
	{
		$this->stateName = $stateName;
		return $this;
	}

	public function __toString(): string
	{
		return $this->stateName;
	}
}
