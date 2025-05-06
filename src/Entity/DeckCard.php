<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DeckCardRepository;
use App\Entity\Card;
use App\Entity\Deck;

#[ORM\Entity(repositoryClass: DeckCardRepository::class)]
#[ORM\Table(name: "deckCard")]

class DeckCard
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: "integer", length: 7, name: "idDeckCard")]
	private $idDeckCard;

	#[ORM\ManyToOne(targetEntity: Deck::class, inversedBy: "idDeck")]
	#[ORM\JoinColumn(name: "idDeck", referencedColumnName: "idDeck")]
	private $idDeck;

	#[ORM\ManyToOne(targetEntity: Card::class, inversedBy: "idCard")]
	#[ORM\JoinColumn(name: "idCard", referencedColumnName: "idCard")]
	private $idCard;

	//----- Getters y setters -----

	public function getIdDeckCard(): ?int
	{
		return $this->idDeckCard;
	}
	public function setIdDeckCard(int $idDeckCard): self
	{
		$this->idDeckCard = $idDeckCard;
		return $this;
	}
	
	public function getIdDeck(): ?int
	{
		return $this->idDeck;
	}
	public function setIdDeck(int $idDeck): self
	{
		$this->idDeck = $idDeck;
		return $this;
	}

	public function getIdCard(): ?int
	{
		return $this->idCard;
	}
	public function setIdCard(int $idCard): self
	{
		$this->idCard = $idCard;
		return $this;
	}
}