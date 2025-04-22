<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
	/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $idUser;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $cardName;

	/**
	 * @ORM\Column(type="string", length=64)
	 */
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