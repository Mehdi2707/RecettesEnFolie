<?php

namespace App\DataFixtures;

use App\Entity\Ingredients;
use App\Entity\Recipes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IngredientsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Hamburger']);

        $ingredient = new Ingredients();
        $ingredient->setName('Steack haché de boeuf');
        $ingredient->setQuantity(1);
        $ingredient->setUnit('pièce');
        $ingredient->setRecipe($recipe);
        $manager->persist($ingredient);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            RecipesFixtures::class
        ];
    }
}
