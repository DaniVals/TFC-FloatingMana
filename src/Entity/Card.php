<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass=CardRepository::class)]
#[ORM\Table(name: "card")]
class Card
{
	#[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "idCard")]
    private $idCard;

	#[ORM\Column(type: "string", length: 255, unique: true, name: "cardName")]
	private $cardName;

	#[ORM\Column(type: "string", length: 64, name: "idScryfall")]
	private $idScryfall;

	//----- Getters y setters -----

	public function getIdUser(): ?int
	{
		return $this->idUser;
	}
	public function setIdUser(int $idUser): self
	{
		$this->idUser = $idUser;
		return $this;
	}

	public function getCardName(): ?string
	{
		return $this->cardName;
	}
	public function setCardName(string $cardName): self
	{
		$this->cardName = $cardName;
		return $this;
	}

	public function getIdScryfall(): ?string
	{
		return $this->idScryfall;
	}
	public function setIdScryfall(string $idScryfall): self
	{
		$this->idScryfall = $idScryfall;
		return $this;
	}
}