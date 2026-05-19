<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $sortie = new Sortie();
        $sortie->setNom('Promenade dans les bois');
        $sortie->setDescription('Description promenade dans les bois');
        $sortie->setDateHeureDebut(new \DateTime());
        $sortie->setDuree(0);
        $sortie->setDateLimiteInscription(new \DateTime());
        $sortie->setNbInscriptionsMax(0);
        $manager->persist($sortie);

        $sortieDeux = new Sortie();
        $sortieDeux->setNom('Voyage dans le nether');
        $sortieDeux->setDescription('Un petit voyage dans le nether');
        $sortieDeux->setDateHeureDebut(new \DateTime());
        $sortieDeux->setDuree(0);
        $sortieDeux->setDateLimiteInscription(new \DateTime());
        $sortieDeux->setNbInscriptionsMax(0);
        $manager->persist($sortieDeux);

        $manager->flush();
    }
}
