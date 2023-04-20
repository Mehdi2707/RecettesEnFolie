<?php

namespace App\DataFixtures;

use App\Entity\Ingredients;
use App\Entity\Recipes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

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

        $faker = Faker\Factory::create('fr_FR');

        for($ingHamburger = 1; $ingHamburger <= 5; $ingHamburger++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Hamburger']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($faker->randomElement(['pièce', 'gramme', 'kilogramme', 'millilitre', 'litre']));
            $ingredient->setRecipe($recipe);
            $manager->persist($ingredient);
        }

        for($ingTarte = 1; $ingTarte <= 5; $ingTarte++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Tarte aux pommes']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($faker->randomElement(['pièce', 'gramme', 'kilogramme', 'millilitre', 'litre']));
            $ingredient->setRecipe($recipe);
            $manager->persist($ingredient);
        }

        for($ingGenoise = 1; $ingGenoise <= 5; $ingGenoise++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Génoise']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($faker->randomElement(['pièce', 'gramme', 'kilogramme', 'millilitre', 'litre']));
            $ingredient->setRecipe($recipe);
            $manager->persist($ingredient);
        }

        for($ingCrepes = 1; $ingCrepes <= 5; $ingCrepes++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Crêpes']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($faker->randomElement(['pièce', 'gramme', 'kilogramme', 'millilitre', 'litre']));
            $ingredient->setRecipe($recipe);
            $manager->persist($ingredient);
        }

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
