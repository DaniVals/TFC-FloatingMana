<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DeckCardRepository;
use App\Entity\Card;
use App\Entity\Deck;

#[ORM\Entity(repositoryClass: DeckCardRepository::class)]
#[ORM\Table(name: "deckcard")]

class DeckCard
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: "integer", name: "idDeckCard")]
	private $idDeckCard;

	#[ORM\ManyToOne(targetEntity: Deck::class, inversedBy: "deckCards", fetch: "EAGER")]
	#[ORM\JoinColumn(name: "idDeck", referencedColumnName: "idDeck")]
	private $deck;

	#[ORM\ManyToOne(targetEntity: Card::class, fetch: "EAGER")]
	#[ORM\JoinColumn(name: "idCard", referencedColumnName: "idCard")]
	private $card;

	#[ORM\Column(type: "integer", length: 3, name: "cardQuantity")]
	private $cardQuantity;

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
	
	public function getDeck(): ?Deck
	{
		return $this->deck;
	}
	public function setDeck(Deck $deck): self
	{
		$this->deck = $deck;
		return $this;
	}

	public function getCard(): ?Card
	{
		return $this->card;
	}
	public function setCard(Card $card): self
	{
		$this->card = $card;
		return $this;
	}

	public function getCardQuantity(): ?int
	{
		return $this->cardQuantity;
	}
	public function setCardQuantity(int $cardQuantity): self
	{
		$this->cardQuantity = $cardQuantity;
		return $this;
	}
}