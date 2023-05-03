<?php

namespace App\DataFixtures;

use App\Entity\Ingredients;
use App\Entity\Recipes;
use App\Entity\Unit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class UnitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $unit = new Unit();
        $unit->setName('pièce');
        $manager->persist($unit);

        $unit = new Unit();
        $unit->setName('gramme');
        $manager->persist($unit);

        $unit = new Unit();
        $unit->setName('litre');
        $manager->persist($unit);

        $unit = new Unit();
        $unit->setName('kilogramme');
        $manager->persist($unit);

        $manager->flush();
    }
}
