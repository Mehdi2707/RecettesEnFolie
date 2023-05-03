<?php

namespace App\DataFixtures;

use App\Entity\Ingredients;
use App\Entity\Recipes;
use App\Entity\Unit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class IngredientsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $unit = $manager->getRepository(Unit::class)->findOneBy(['name' => 'pièce']);
        $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Hamburger']);

        $ingredient = new Ingredients();
        $ingredient->setName('Steack haché de boeuf');
        $ingredient->setQuantity(1);
        $ingredient->setUnit($unit);
        $ingredient->addRecipe($recipe);
        $manager->persist($ingredient);

        $faker = Faker\Factory::create('fr_FR');

        for($ingHamburger = 1; $ingHamburger <= 5; $ingHamburger++)
        {
            $unit = $manager->getRepository(Unit::class)->findOneBy(['name' => 'pièce']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($unit);
            $manager->persist($ingredient);
        }

        for($ingTarte = 1; $ingTarte <= 5; $ingTarte++)
        {
            $unit = $manager->getRepository(Unit::class)->findOneBy(['name' => 'gramme']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($unit);
            $manager->persist($ingredient);
        }

        for($ingGenoise = 1; $ingGenoise <= 5; $ingGenoise++)
        {
            $unit = $manager->getRepository(Unit::class)->findOneBy(['name' => 'litre']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($unit);
            $manager->persist($ingredient);
        }

        for($ingCrepes = 1; $ingCrepes <= 5; $ingCrepes++)
        {
            $unit = $manager->getRepository(Unit::class)->findOneBy(['name' => 'kilogramme']);
            $ingredient = new Ingredients();
            $ingredient->setName($faker->word());
            $ingredient->setQuantity($faker->randomFloat(2, 1, 1000));
            $ingredient->setUnit($unit);
            $manager->persist($ingredient);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            RecipesFixtures::class,
            UnitFixtures::class
        ];
    }
}
