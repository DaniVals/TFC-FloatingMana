<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity] 
#[ORM\Table(name: 'collection')]
class Collection 
{	
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
     */
    private $idCollection;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="idUser")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idUser;

    /**
     * @ORM\ManyToMany(targetEntity=Card::class, inversedBy="idCard")
     */
    private $idCard;

	//----- Constructor -----

    public function __construct()
    {
        $this->idCard = new ArrayCollection();
    }

	//----- Getters y setters -----

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }
}
