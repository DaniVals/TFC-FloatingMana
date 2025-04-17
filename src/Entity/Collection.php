<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\UserCollectionRepository;
use App\Entity\Card;
use App\Entity\User;


/**
 * @ORM\Entity(repositoryClass=UserCollectionRepository::class)
 */
class Collection 
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type:'integer', name:'idCollection')]
	private $idCollection;
	
	#[ORM\Column(type:'string', name:'collectionOwner')]
	private $collectionOwner;

	#[ORM\Column(type:'string', name:'collCardList ')]
	private $collCardList ;

	#[ORM\Column(type:'number', name:'collectionValue')]
	private $collectionValue;

	#[ORM\Column(type:'integer', name:'cardAmount')]
	private $cardAmount;

    // /**
    //  * @ORM\Id
    //  * @ORM\GeneratedValue
    //  * @ORM\Column(type="integer")
    //  */
    // private $id;

    // /**
    //  * @ORM\ManyToOne(targetEntity=User::class, inversedBy="collections")
    //  * @ORM\JoinColumn(nullable=false)
    //  */
    // private $user;

    // /**
    //  * @ORM\ManyToMany(targetEntity=Card::class, inversedBy="collections")
    //  */
    // private $cards;

	//----- Constructor -----

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

	//----- Getters y setters -----

    public function getId(): ?int
    {
        return $this->id;
    }
}
