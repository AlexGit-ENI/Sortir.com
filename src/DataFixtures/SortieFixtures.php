<?php

namespace App\DataFixtures;

use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
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
        $sortie->setSite($this->getReference(SiteFixtures::$campus[1], Site::class));
        $manager->persist($sortie);

        $sortie = new Sortie();
        $sortie->setNom('Voyage dans le nether');
        $sortie->setDescription('Un petit voyage dans le nether');
        $sortie->setDateHeureDebut(new \DateTime());
        $sortie->setDuree(0);
        $sortie->setDateLimiteInscription(new \DateTime());
        $sortie->setNbInscriptionsMax(0);
        $sortie->setSite($this->getReference(SiteFixtures::$campus[3], Site::class));
        $manager->persist($sortie);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SiteFixtures::class];
    }
}
