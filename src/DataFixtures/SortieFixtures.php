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

        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 1; $i < 15; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->words(3, true));
            $sortie->setDescription($faker->sentence(3, true));
            $sortie->setDateHeureDebut(new \DateTime());
            $sortie->setDuree(random_int(1, 4));
            $sortie->setDateLimiteInscription(new \DateTime());
            $sortie->setNbInscriptionsMax(random_int(4, 12));
            $sortie->setSite($this->getReference(SiteFixtures::$campus[random_int(0, count(SiteFixtures::$campus) - 1)], Site::class));
            $manager->persist($sortie);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SiteFixtures::class];
    }
}
