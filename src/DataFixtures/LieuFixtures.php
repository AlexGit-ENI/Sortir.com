<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{

    public static array $lieuKeys = [];
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        // $product = new Product();
        // $manager->persist($product);

        for ($i = 1; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->words(3, true));
            $lieu->setRue($faker->streetAddress());
            $lieu->setLatitude($faker->latitude());
            $lieu->setLongitude($faker->longitude());
            $lieu->setVille($this->getReference(VilleFixtures::$villeKeys[random_int(0, count(VilleFixtures::$villeKeys) - 1)], Ville::class));
            static::$lieuKeys[$i-1] = $lieu->getNom();
            $this->addReference(static::$lieuKeys[$i-1], $lieu);
            $manager->persist($lieu);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [VilleFixtures::class];
    }
}
