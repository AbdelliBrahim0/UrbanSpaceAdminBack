<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:read', 'user:list'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le nom complet est obligatoire.")]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email n'est pas valide.")]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le numero du telephone est obligatoire.")]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "L'adresse à domicile est obligatoire.")]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?string $adresse = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?array $roles = [];

    #[ORM\Column]
    #[Groups(['user:read', 'user:list'])]
    private ?\DateTimeImmutable $dateInscription = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?string $provider = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?string $providerId = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    #[Groups(['user:read', 'user:write', 'user:list'])]
    private ?string $avatar = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTimeImmutable();
        $this->roles = ['ROLE_USER'];
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(?array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeImmutable $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function setProviderId(?string $providerId): static
    {
        $this->providerId = $providerId;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function isOAuthUser(): bool
    {
        return $this->provider !== null && $this->providerId !== null;
    }
}