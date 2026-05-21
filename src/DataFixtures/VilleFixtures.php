<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public static array $villeKeys = [];

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        // $product = new Product();
        // $manager->persist($product);

        for ($i = 0; $i <= 10; $i++) {
            $ville = new Ville();
            $ville->setNom($faker->city());
            $ville->setCodePostal($faker->postcode());
            static::$villeKeys[$i] = $ville->getNom();
            $this->addReference(static::$villeKeys[$i], $ville);
            $manager->persist($ville);
        }


        $manager->flush();
    }
}
