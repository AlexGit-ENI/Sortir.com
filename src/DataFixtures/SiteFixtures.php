<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public static array $campus = [
        'Quimper',
        'Rennes',
        'Nantes',
        'Niort'
    ];

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

    $siteQuimper = new Site();
    $siteQuimper->setNom('Quimper');
    $this->addReference(static::$campus[0], $siteQuimper);
    $manager->persist($siteQuimper);

    $siteRennes = new Site();
    $siteRennes->setNom('Rennes');
    $this->addReference(static::$campus[1], $siteRennes);
    $manager->persist($siteRennes);

    $siteNantes = new Site();
    $siteNantes->setNom('Nantes');
    $this->addReference(static::$campus[2], $siteNantes);
    $manager->persist($siteNantes);

    $siteNiort = new Site();
    $siteNiort->setNom('Niort');
    $this->addReference(static::$campus[3], $siteNiort);
    $manager->persist($siteNiort);


    $manager->flush();
    }
}
