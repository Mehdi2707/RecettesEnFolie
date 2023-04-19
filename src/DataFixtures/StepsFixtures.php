<?php

namespace App\DataFixtures;

use App\Entity\Recipes;
use App\Entity\Steps;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StepsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $recipe = $manager->getRepository(Recipes::class)->findOneBy(['title' => 'Hamburger']);

        $step = new Steps();
        $step->setDescription('Prenez le pain hamburger puis mettez vos ingrédients');
        $step->setRecipe($recipe);
        $manager->persist($step);

        $manager->flush();
    }
}
