<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
         $user = new User();
         $user->setEmail('admin@admin.com');
         $user->setRoles([User::ROLE_ADMIN]);
         $hashedPassword = $this->passwordHasher->hashPassword(
             $user,
             'admin'
         );
         $user->setPassword($hashedPassword);

         $manager->persist($user);

        $manager->flush();
    }
}
