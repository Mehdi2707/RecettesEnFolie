<?php

namespace App\DataFixtures;

use App\Entity\Ingredients;
use App\Entity\Recipes;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class RecipesFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(Users::class)->findOneBy(['username' => 'Mehdi']);
        $ingredient = $manager->getRepository(Ingredients::class)->findOneBy(['name' => 'Steack haché de boeuf']);

        $recipe = new Recipes();
        $recipe->setTitle('Test');
        $recipe->setDescription('test');
        $recipe->setSlug($this->slugger->slug($recipe->getTitle())->lower());
        $recipe->setPreparationTime(10);
        $recipe->setCookingTime(10);
        $recipe->setNumberOfServings(4);
        $recipe->setDifficultyLevel('facile');
        $recipe->addIngredient($ingredient);
        $recipe->setUser($user);
        $manager->persist($recipe);

        $this->createRecipe('Hamburger', 'Délicieux hamburger !', 20, 10, 4, 'facile', $user, $manager);
        $this->createRecipe('Tarte aux pommes', 'Tarte aux pommes healthy et gourmande', 20, 15, 6, 'facile', $user, $manager);
        $this->createRecipe('Génoise', 'Génoise légère', 10, 20, 6, 'facile', $user, $manager);
        $this->createRecipe('Crêpes', 'Crêpes facile', 10, 60, 8, 'facile', $user, $manager);

        $manager->flush();
    }

    public function createRecipe(string $title, string $description, int $preparationTime, int $cookingTime, int $numberOfServings, string $difficulty, Users $user, ObjectManager $manager)
    {
        $recipe = new Recipes();
        $recipe->setTitle($title);
        $recipe->setDescription($description);
        $recipe->setSlug($this->slugger->slug($recipe->getTitle())->lower());
        $recipe->setPreparationTime($preparationTime);
        $recipe->setCookingTime($cookingTime);
        $recipe->setNumberOfServings($numberOfServings);
        $recipe->setDifficultyLevel($difficulty);
        $recipe->setUser($user);
        $manager->persist($recipe);

        return $recipe;
    }
}
