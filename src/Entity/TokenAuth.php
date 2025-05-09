<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use DateTime;

#[ORM\Entity]
#[ORM\Table(name: 'tokenauth')]

class TokenAuth
{
	#[ORM\Id]
	#[ORM\OneToOne(targetEntity: User::class, inversedBy: 'id')]
	#[ORM\JoinColumn(name: 'idUser', referencedColumnName: 'idUser')]
	private int $idUser;

	#[ORM\Column(type: 'string', length: 255, name: 'token')]
	private string $token;

	#[ORM\Column(type: 'datetime', name: 'expirationDate')]
	private DateTime $expirationDate;

	// ----- Getters y Setters -----

	public function getIdUser(): int
	{
		return $this->idUser;
	}
	public function setIdUser(int $idUser): self
	{
		$this->idUser = $idUser;
		return $this;
	}

	public function getToken(): string
	{
		return $this->token;
	}
	public function setToken(string $token): self
	{
		$this->token = $token;
		return $this;
	}

	public function getExpirationDate(): \DateTime
	{
		return $this->expirationDate;
	}
	public function setExpirationDate(\DateTime $expirationDate): self
	{
		$this->expirationDate = $expirationDate;
		return $this;
	}
}