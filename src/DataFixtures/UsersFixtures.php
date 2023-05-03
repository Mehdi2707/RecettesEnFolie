<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class UsersFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Users();
        $admin->setUsername('Mehdi');
        $admin->setEmail('d38.h4ck3ur@live.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Falkor12'));
        $admin->setResetToken('');
        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');

        for($users = 1; $users <= 5; $users++)
        {
            $user = new Users();
            $user->setUsername($faker->userName);
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'azerty'));
            $user->setResetToken('');
            $manager->persist($user);
        }

        $manager->flush();
    }
}
