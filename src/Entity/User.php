<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true) */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private $password;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\Column(type="datetime")
     */
    // private $createdAt;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    // private $lastLoginAt;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $active = true;
    
    /**
     * @ORM\Column(type="integer")
     */
    // private $failedLoginAttempts = 0;

    // Getters y setters...
    
    public function getId(): ?int
    {
        return $this->id;
    
    }

    public function setId(int $id): self
    {
        $this->id = $id;
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
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    
    public function eraseCredentials() : void 
    {
        // Si almacenas datos temporalmente, bórralos aquí
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    public function isActive(): bool
    {
        return $this->active;
    }
    
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
    
    // public function getFailedLoginAttempts(): int
    // {
    //     return $this->failedLoginAttempts;
    // }
    // 
    // public function setFailedLoginAttempts(int $attempts): self
    // {
    //     $this->failedLoginAttempts = $attempts;
    //     return $this;
    // }
    // 
    // public function getLastLoginAt(): ?\DateTimeInterface
    // {
    //     return $this->lastLoginAt;
    // }
    // 
    // public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    // {
    //     $this->lastLoginAt = $lastLoginAt;
    //     return $this;
    // }
    // 
    // public function getCreatedAt(): ?\DateTimeInterface
    // {
    //     return $this->createdAt;
    // }
    // 
    // public function setCreatedAt(\DateTimeInterface $createdAt): self
    // {
    //     $this->createdAt = $createdAt;
    //     return $this;
    // }
}
