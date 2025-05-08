<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DeckRepository;
use App\Entity\User;

#[ORM\Entity(repositoryClass: DeckRepository::class)]
#[ORM\Table(name: "deck")]

class Deck
{
	#[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", length: 7, name: "idDeck")]
	private $idDeck;
	
	#[ORM\OneToMany(targetEntity: DeckCard::class, mappedBy: "deck")]
	private $deckCards;

    #[ORM\Column(type: "string", length: 50, name: "deckName")]
    private $deckName;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: "decks")]
	#[ORM\JoinColumn(name: "idUser", referencedColumnName: "username")]
    private $user;

	#[ORM\Column(type: "string", length: 150, name: "type")]
	private $type;

	//----- Getters y setters -----

	public function getIdDeck(): ?int
	{
		return $this->idDeck;
	}

	public function getDeckName(): ?string
	{
		return $this->deckName;
	}
	public function setDeckName(string $deckName): self
	{
		$this->deckName = $deckName;
		return $this;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}
	public function setUser(User $user): self
	{
		$this->user = $user;
		return $this;
	}

	public function getType(): ?string
	{
		return $this->type;
	}
	public function setType(string $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function getDeckCards() 
	{
		return $this->deckCards;
	}
	public function setDeckCards(array $deckCards): self
	{
		$this->deckCards = $deckCards;
		return $this;
	}
}
