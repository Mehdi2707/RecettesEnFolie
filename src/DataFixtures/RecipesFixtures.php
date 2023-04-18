<?php

namespace App\DataFixtures;

use App\Entity\Recipes;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RecipesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $recipe = new Recipes();
        $recipe->setTitle('Hamburger');
        $recipe->setDescription('Délicieux hamburger !');
        $recipe->setSlug('hamburger');
        $recipe->setPreparationTime(20);
        $recipe->setCookingTime(10);
        $recipe->setNumberOfServings(4);
        $recipe->setDifficultyLevel('facile');
        $recipe->setUserId(3);

        $manager->persist($recipe);

        $manager->flush();
    }
}
