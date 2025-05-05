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
	#[ORM\OneToMany(targetEntity: DeckCard::class, mappedBy: "idDeck")]
	private $idDeck;

    #[ORM\Column(type: "string", length: 50, name: "deckName")]
    private $deckName;

    #[ORM\Column(type: "string", length: 50, name: "idUser")]
	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: "username")]
    private $idUser;

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

	public function getIdUser(): ?string
	{
		return $this->idUser;
	}
	public function setIdUser(string $idUser): self
	{
		$this->idUser = $idUser;
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
}
