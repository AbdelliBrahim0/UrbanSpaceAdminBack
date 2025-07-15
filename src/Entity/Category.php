<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category')]
#[UniqueEntity(fields: ['nom'], message: 'Cette catégorie existe déjà.')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['category:read', 'category:list'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le nom de la catégorie est obligatoire.")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit contenir au moins 2 caractères.")]
    #[Groups(['category:read', 'category:write', 'category:list'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['category:read', 'category:write'])]
    private ?string $description = null;

    // Relation avec les sous-catégories
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'sousCategories')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['category:read', 'category:write'])]
    private ?self $categorieParent = null;

    #[ORM\OneToMany(mappedBy: 'categorieParent', targetEntity: self::class)]
    #[Groups(['category:read'])]
    private Collection $sousCategories;

    public function __construct()
    {
        $this->sousCategories = new ArrayCollection();
    }

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

    public function getCategorieParent(): ?self
    {
        return $this->categorieParent;
    }

    public function setCategorieParent(?self $categorieParent): static
    {
        $this->categorieParent = $categorieParent;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSousCategories(): Collection
    {
        return $this->sousCategories;
    }

    public function addSousCategory(self $sousCategory): static
    {
        if (!$this->sousCategories->contains($sousCategory)) {
            $this->sousCategories->add($sousCategory);
            $sousCategory->setCategorieParent($this);
        }
        return $this;
    }

    public function removeSousCategory(self $sousCategory): static
    {
        if ($this->sousCategories->removeElement($sousCategory)) {
            if ($sousCategory->getCategorieParent() === $this) {
                $sousCategory->setCategorieParent(null);
            }
        }
        return $this;
    }

    public function isParent(): bool
    {
        return $this->categorieParent === null;
    }

    public function hasChildren(): bool
    {
        return !$this->sousCategories->isEmpty();
    }

    public function getFullName(): string
    {
        if ($this->categorieParent) {
            return $this->categorieParent->getNom() . ' > ' . $this->nom;
        }
        return $this->nom;
    }
}