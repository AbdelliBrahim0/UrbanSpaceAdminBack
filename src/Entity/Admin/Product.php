<?php

namespace App\Entity\Admin;

use App\Repository\Admin\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read', 'product:list'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?string $description = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?float $prix = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La catégorie est obligatoire.")]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?Category $categorie = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?array $tailles = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?array $couleurs = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(message: "Le stock est obligatoire.")]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?int $stock = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    #[Groups(['product:read', 'product:write', 'product:list'])]
    private ?string $imageUrl = null;

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
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }
    public function getPrix(): ?float
    {
        return $this->prix;
    }
    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }
    public function getCategorie(): ?Category
    {
        return $this->categorie;
    }
    public function setCategorie(?Category $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }
    public function getTailles(): ?array
    {
        return $this->tailles;
    }
    public function setTailles(?array $tailles): static
    {
        $this->tailles = $tailles;
        return $this;
    }
    public function getCouleurs(): ?array
    {
        return $this->couleurs;
    }
    public function setCouleurs(?array $couleurs): static
    {
        $this->couleurs = $couleurs;
        return $this;
    }
    public function getStock(): ?int
    {
        return $this->stock;
    }
    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }
    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }
}
