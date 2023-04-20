<?php

namespace App\DataFixtures;

use App\Entity\Recipes;
use App\Entity\Steps;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class StepsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Hamburger']);

        $step = new Steps();
        $step->setDescription('Prenez le pain hamburger puis mettez vos ingrédients');
        $step->setRecipe($recipe);
        $manager->persist($step);

        $faker = Faker\Factory::create('fr_FR');

        for($stepHamburger = 1; $stepHamburger <= 3; $stepHamburger++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Hamburger']);
            $step = new Steps();
            $step->setDescription($faker->paragraph());
            $step->setRecipe($recipe);
            $manager->persist($step);
        }

        for($stepTarte = 1; $stepTarte <= 4; $stepTarte++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Tarte aux pommes']);
            $step = new Steps();
            $step->setDescription($faker->paragraph());
            $step->setRecipe($recipe);
            $manager->persist($step);
        }

        for($stepGenoise = 1; $stepGenoise <= 4; $stepGenoise++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Génoise']);
            $step = new Steps();
            $step->setDescription($faker->paragraph());
            $step->setRecipe($recipe);
            $manager->persist($step);
        }

        for($stepCrepes = 1; $stepCrepes <= 4; $stepCrepes++)
        {
            $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Crêpes']);
            $step = new Steps();
            $step->setDescription($faker->paragraph());
            $step->setRecipe($recipe);
            $manager->persist($step);
        }

        $manager->flush();
    }
}
