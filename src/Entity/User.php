<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity] 
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=6)
     */
    private $idUser;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $email;
	
    /**
	 * @ORM\Column(type="string", length=255)
     */
	private $password;

	/**
     * @ORM\Column(type="integer", length=1)
     */
    private $isAuth = 0;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     */
	private $profilePic = null;

//----- Campos que no están en la base de datos -----
	
	/**
	 * @ORM\Column(type="json")
	 */
	private $roles = [];
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoginAt;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $failedLoginAttempts = 0;

//----- Getters y setters ----
    
    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

	public function getUsername(): ?string
    {
        return $this->username;
    }
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

	public function getPassword(): string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

	public function isActive(): int
    {
        return $this->isAuth;
    }
    
    public function setActive(int $isAuth): self
    {
        $this->isAuth = $isAuth;
        return $this;
    }

	public function getProfilePic(): ?string
    {
        return $this->profilePic;
    }
    public function setProfilePic(string $profilePic): self
    {
        $this->profilePic = $profilePic;
        return $this;
    }
    
//----- Getters y setters de campos que no están en la bd ----
    
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantizar que todos los usuarios tengan ROLE_USER
        $roles[] = 'ROLE_USER';
        
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }
    
    public function eraseCredentials() : void 
    {
        // Si almacenas datos temporalmente, bórralos aquí
    }
    
    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }
    public function setFailedLoginAttempts(int $attempts): self
    {
        $this->failedLoginAttempts = $attempts;
        return $this;
    }
    
    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }
    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
