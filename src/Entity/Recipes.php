<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\UpdatedAtTrait;
use App\Repository\RecipesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecipesRepository::class)]
class Recipes
{
    use CreatedAtTrait;
    use UpdatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre de la recette ne peut pas être vide')]
    #[Assert\Length(min: 5, max: 50, minMessage: 'Le titre doit faire au moins {{ limit }} caractères', maxMessage: 'Le titre ne doit pas faire plus de {{ limit }} caractères')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'Le temps de préparation ne peut pas être inférieur à 1')]
    private ?int $preparationTime = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'Le temps de cuisson ne peut pas être négatif')]
    private ?int $cookingTime = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'Le nombre de portions ne peut pas être inférieur à 1')]
    private ?int $numberOfServings = null;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToMany(targetEntity: Ingredients::class, inversedBy: 'recipes', cascade: ['persist'])]
    private Collection $ingredients;

    #[ORM\OneToMany(mappedBy: 'recipes', targetEntity: Steps::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $steps;

    #[ORM\OneToMany(mappedBy: 'recipes', targetEntity: Comments::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'recipes', targetEntity: Favorites::class, orphanRemoval: true)]
    private Collection $favorites;

    #[ORM\OneToMany(mappedBy: 'recipes', targetEntity: Images::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: Notes::class, orphanRemoval: true)]
    private Collection $notes;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DifficultyLevel $difficultyLevel = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->images = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPreparationTime(): ?int
    {
        return $this->preparationTime;
    }

    public function setPreparationTime(int $preparationTime): self
    {
        $this->preparationTime = $preparationTime;

        return $this;
    }

    public function getCookingTime(): ?int
    {
        return $this->cookingTime;
    }

    public function setCookingTime(int $cookingTime): self
    {
        $this->cookingTime = $cookingTime;

        return $this;
    }

    public function getNumberOfServings(): ?int
    {
        return $this->numberOfServings;
    }

    public function setNumberOfServings(int $numberOfServings): self
    {
        $this->numberOfServings = $numberOfServings;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Ingredients>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredients $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
        }

        return $this;
    }

    public function removeIngredient(Ingredients $ingredient): self
    {
        $this->ingredients->removeElement($ingredient);

        return $this;
    }

    /**
     * @return Collection<int, Steps>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Steps $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setRecipes($this);
        }

        return $this;
    }

    public function removeStep(Steps $step): self
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getRecipes() === $this) {
                $step->setRecipes(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setRecipes($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getRecipes() === $this) {
                $comment->setRecipes(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favorites>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorites $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->setRecipes($this);
        }

        return $this;
    }

    public function removeFavorite(Favorites $favorite): self
    {
        if ($this->favorites->removeElement($favorite)) {
            // set the owning side to null (unless already changed)
            if ($favorite->getRecipes() === $this) {
                $favorite->setRecipes(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Images>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setRecipes($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getRecipes() === $this) {
                $image->setRecipes(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notes>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Notes $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setRecipe($this);
        }

        return $this;
    }

    public function removeNote(Notes $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getRecipe() === $this) {
                $note->setRecipe(null);
            }
        }

        return $this;
    }

    public function getDifficultyLevel(): ?DifficultyLevel
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(?DifficultyLevel $difficultyLevel): self
    {
        $this->difficultyLevel = $difficultyLevel;

        return $this;
    }
}
