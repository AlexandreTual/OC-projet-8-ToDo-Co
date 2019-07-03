<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user
            ->setUsername('user')
            ->setPassword('password')
            ->setEmail('user@todo-co.com')
            ->setRoles('ROLE_USER');

        $manager->persist($user);
        $manager->flush();
    }
}
