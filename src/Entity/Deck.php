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
    #[ORM\Column(type: "integer", name: "idDeck")]
	private $idDeck;
	
	#[ORM\OneToMany(targetEntity: DeckCard::class, mappedBy: "deck", fetch: "EAGER")]
	private $deckCards;

    #[ORM\Column(type: "string", length: 50, name: "deckName")]
    private $deckName;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: "decks", fetch: "EAGER")]
	#[ORM\JoinColumn(name: "idUser", referencedColumnName: "idUser")]
    private $user;

	#[ORM\Column(type: "string", length: 150, name: "format")]
	private $format;

	#[ORM\Column(type: "string", length: 255, name: "coverImg")]
	private $coverImg;

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

	public function getFormat(): ?string
	{
		return $this->format;
	}
	public function setFormat(string $format): self
	{
		$this->format = $format;
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

	public function getCoverImg(): ?string
	{
		return $this->coverImg;
	}
	public function setCoverImg(string $coverImg): self
	{
		$this->coverImg = $coverImg;
		return $this;
	}
}
