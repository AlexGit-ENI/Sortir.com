<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public static array $villeKeys = [
        'Quimper',
        'Rennes',
        'Vannes',
        'Nantes'
    ];

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $quimper = new Ville();
        $quimper->setNom('Quimper');
        $quimper->setCodePostal('29000');

        $this->addReference(static::$villeKeys[0], $quimper);
        $manager->persist($quimper);

        $rennes = new Ville();
        $rennes->setNom('Rennes');
        $rennes->setCodePostal('35000');

        $this->addReference(static::$villeKeys[1], $rennes);
        $manager->persist($rennes);

        $vannes = new Ville();
        $vannes->setNom('Vannes');
        $vannes->setCodePostal('56000');

        $this->addReference(static::$villeKeys[2], $vannes);
        $manager->persist($vannes);

        $nantes = new Ville();
        $nantes->setNom('Nantes');
        $nantes->setCodePostal('44000');

        $this->addReference(static::$villeKeys[3], $nantes);
        $manager->persist($nantes);

        $manager->flush();
    }
}
