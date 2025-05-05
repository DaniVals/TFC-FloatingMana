<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CardRepository;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\Table(name: "card")]

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\Table(name: "card")]
class Card
{
	#[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "idCard")]
    private $idCard;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $cardName;

	#[ORM\Column(type: "string", length: 64, name: "idScryfall")]
	private $idScryfall;

	//----- Getters y setters -----

	public function getIdCard(): ?int
	{
		return $this->idCard;
	}
	public function setIdCard(int $idCard): self
	{
		$this->idCard = $idCard;
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