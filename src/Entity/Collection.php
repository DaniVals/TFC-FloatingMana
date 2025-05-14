<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\UserCollectionRepository;
use App\Entity\Card;
use App\Entity\User;
use App\Entity\State;

#[ORM\Entity(repositoryClass: UserCollectionRepository::class)]
#[ORM\Table(name: "collection")]

class Collection 
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "idCollection")]
    private $idCollection;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "collectionCards", fetch: "EAGER")]
	#[ORM\JoinColumn(name: "idUser", referencedColumnName: "idUser")]
    private $user;

	// #[ORM\JoinColumn(name: "idCard", referencedColumnName: "idCard")]
    #[ORM\ManyToMany(targetEntity: Card::class)]
	#[ORM\JoinTable(
		name: "collection_card",
		joinColumns: [
			new ORM\JoinColumn(name: "idCollection", referencedColumnName: "idCollection")
		],
		inverseJoinColumns: [
			new ORM\JoinColumn(name: "idCard", referencedColumnName: "idCard")
		]
	)]
    private $card;

	#[ORM\Column(type: "decimal", precision: 6, scale: 2, name: "purchasePrice")]
	private $purchasePrice;

	#[ORM\Column(type: "integer", length: 1, name: "isFoil")]
	private $isFoil;
	
	#[ORM\ManyToOne(targetEntity: State::class)]
	#[ORM\JoinColumn(name: "state", referencedColumnName: "idState")]
	private $state;
	
	//----- Variables auxiliares -----

	private $cards;
    private $name; // Added name property 

	//----- Getters y setters -----

	public function getIdCollection(): ?int
	{
		return $this->idCollection;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}
	public function setUser(?User $user): self
	{
		$this->user = $user;
		return $this;
	}

	public function getCard(): ?ArrayCollection
	{
		return $this->card;
	}
	public function setCard(ArrayCollection $card): self
	{
		$this->card = $card;
		return $this;
	}

	public function getPurchasePrice(): ?string
	{
		return $this->purchasePrice;
	}
	public function setPurchasePrice(string $purchasePrice): self
	{
		$this->purchasePrice = $purchasePrice;
		return $this;
	}

	public function getIsFoil(): ?int
	{
		return $this->isFoil;
	}
	public function setIsFoil(int $isFoil): self
	{
		$this->isFoil = $isFoil;
		return $this;
	}

	public function getState(): ?State
	{
		return $this->state;
	}
	public function setState(State $state): self
	{
		$this->state = $state;
		return $this;
	}

    // Added name getter and setter
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

	//----- Funciones -----
	
	/**
	 * @return ArrayCollection|Card[]
	 */
	public function getCards()
	{
		return $this->cards;
	}

	public function addCard(Card $card): self
	{
		if (!$this->cards->contains($card)) {
			$this->cards[] = $card;
		}
		return $this;
	}

	public function removeCard(Card $card): self
	{
		$this->cards->removeElement($card);
		return $this;
	}

	public function toArray(): array {
		return [
			'user' => $this->getUser()?->getName(),
			'idCollection' => $this->getIdCollection(),
			'cards' => array_map(
				fn($card) => $card->toArray(),
				$this->getCards()->toArray()
			),
			'purchasePrice' => $this->getPurchasePrice(),
			'isFoil' => $this->getIsFoil(),
			'state' => $this->getState()?->getStateName(),
			'name' => $this->getName() // Added name to the array output
		];
	}

	public function __construct() {
		$this->cards = new ArrayCollection();
	}
}
