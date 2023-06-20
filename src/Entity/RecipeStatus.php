<?php

namespace App\Entity;

use App\Repository\RecipeStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeStatusRepository::class)]
class RecipeStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, options: ['default' => 'en attente'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\OneToMany(mappedBy: 'recipe_status', targetEntity: Recipes::class)]
    private Collection $recipeStatus;

    public function __construct()
    {
        $this->recipeStatus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection<int, Recipes>
     */
    public function getRecipeStatus(): Collection
    {
        return $this->recipeStatus;
    }

    public function addRecipeStatus(Recipes $recipeStatus): self
    {
        if (!$this->recipeStatus->contains($recipeStatus)) {
            $this->recipeStatus->add($recipeStatus);
            $recipeStatus->setRecipeStatus($this);
        }

        return $this;
    }

    public function removeRecipeStatus(Recipes $recipeStatus): self
    {
        if ($this->recipeStatus->removeElement($recipeStatus)) {
            // set the owning side to null (unless already changed)
            if ($recipeStatus->getRecipeStatus() === $this) {
                $recipeStatus->setRecipeStatus(null);
            }
        }

        return $this;
    }
}
