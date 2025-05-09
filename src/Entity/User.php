<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Deck;
use App\Entity\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "idUser")]
    private $id;

	#[ORM\Column(type: "string", length: 255, name: "username")]
	private $name;
	
	// ----- Relación de username con otras entidades -----

	#[ORM\OneToMany(targetEntity: Collection::class, mappedBy: "user")]
	private $collectionCards;
	
	#[ORM\OneToMany(targetEntity: Deck::class, mappedBy: "user", fetch: "EAGER")]
	private $decks;

	//----- Fin de la relación de username con otras entidades -----

    #[ORM\Column(type: "string", length: 50, unique: true, name: "email")]
    private $email;
    
    private $roles = []; // Este campo es necesario si se usan roles dinámicos

    #[ORM\Column(type: "string", length: 255, name: "password")]
    private $password;

    #[ORM\Column(type: "boolean", options: ["default" => false], name: "isAuth")]
    private $isAuth = false;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "profilePic")]
    private $profilePic;

    private $active = true;

    // Comentado porque no está en uso actualmente
    // #[ORM\Column(type: "datetime", name: "createdAt")]
    // private $createdAt;

    // Comentado porque no está en uso actualmente
    // #[ORM\Column(type: "datetime", nullable: true, name: "lastLoginAt")]
    // private $lastLoginAt;

    // Comentado porque no está en uso actualmente
    // #[ORM\Column(type: "integer", name: "failedLoginAttempts")]
    private $failedLoginAttempts = 0;

	// ----- Constructor -----

	public function __construct()
	{
		// Inicializa las colecciones de cartas y mazos
		$this->collectionCards = new ArrayCollection();
		$this->decks = new ArrayCollection();
	}

    // -----Getters y setters-----

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
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

	public function getCollectionCards()
	{
		return $this->collectionCards;
	}
	public function setCollectionCards(array $collectionCards): self
	{
		$this->collectionCards = $collectionCards;
		return $this;
	}

	public function getDecks()
	{
		return $this->decks;
	}
	public function setDecks(array $decks): self
	{
		$this->decks = $decks;
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

    // Comentado porque si no usas roles complejos, puedes forzar ROLE_USER
    // public function getRoles(): array
    // {
    //     $roles = $this->roles;
         // Garantizar que todos los usuarios tengan ROLE_USER
    //     $roles[] = 'ROLE_USER';
    //
    //     return array_unique($roles);
    // }

    // Si no planeas usar roles adicionales, puedes simplemente devolver ROLE_USER aquí:
    public function getRoles(): array
    {
        return ['ROLE_USER'];
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

    public function eraseCredentials(): void
    {
        // Si almacenas datos temporales, bórralos aquí
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

    public function getIsAuth(): bool
    {
        return $this->isAuth;
    }

    public function setIsAuth(bool $isAuth): self
    {
        $this->isAuth = $isAuth;
        return $this;
    }

    public function getProfilePic(): ?string
    {
        return $this->profilePic;
    }

    public function setProfilePic(?string $profilePic): self
    {
        $this->profilePic = $profilePic;
        return $this;
    }

    // Comentado porque no está en uso actualmente
    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function setFailedLoginAttempts(int $attempts): self
    {
        $this->failedLoginAttempts = $attempts;
        return $this;
    }
    // 
    // Comentado porque no está en uso actualmente
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
    // Comentado porque no está en uso actualmente
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

