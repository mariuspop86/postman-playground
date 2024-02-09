<?php

namespace App\DataFixtures;

use App\Entity\Product;
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
        $user = $this->createUser();
        $manager->persist($user);

        foreach ($this->createProducts() as $product) {
            $manager->persist($product);
        }

        $manager->flush();
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('admin@admin.com');
        $user->setRoles([User::ROLE_ADMIN]);
        $hashedPassword = $this->passwordHasher->hashPassword(
         $user,
         'admin'
        );
        $user->setPassword($hashedPassword);
        $user->setAvatar('god.png');

        return $user;
    }

    private function createProduct(string $name, string $description, int $price): Product
    {
        $product = new Product();
        $product->setName($name);
        $product->setDescription($description);
        $product->setPrice($price);

        return $product;
    }

    private function createProducts(): array
    {
        return [
            $this->createProduct(
                'LuminoGlow Night Serum',
                'Experience the magic of LuminoGlow Night Serum, a revolutionary skincare solution designed to transform your skin while you sleep.',
                3999
            ),
            $this->createProduct(
                'ZenTranquil Aromatherapy Diffuser',
                'Create a serene oasis in your home with the ZenTranquil Aromatherapy Diffuser. Utilizing ultrasonic technology, this stylish diffuser releases a fine mist of your favorite essential oils, filling your space with calming aromas and promoting relaxation.',
                4995
            ),
            $this->createProduct(
                'EcoFresh Bamboo Toothbrush Set',
                'Go green with the EcoFresh Bamboo Toothbrush Set, the eco-friendly alternative to traditional plastic toothbrushes.',
                1299
            ),
        ];
    }
}
