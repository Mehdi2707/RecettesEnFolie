<?php

namespace App\Entity;

use App\Repository\RecipeStatusRepository;
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

    #[ORM\ManyToOne(inversedBy: 'recipeStatus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recipes $recipe = null;

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

    public function getRecipe(): ?Recipes
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipes $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }
}
