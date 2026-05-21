<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }
    public static array $participantKeys = [];
    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create('fr_FR');

        ////////////////////////Admin///////////////////////////////
        $participant = new Participant();
        $participant->setUsername('admin');
        $participant->setNom('ADMIN');
        $participant->setPrenom('Stephane');
        $participant->setTelephone('0000000000');
        $participant->setMail('admin@admin.test');
        $participant->setAdministrateur(true);
        $participant->setActif(true);
        $participant->setPassword($this->passwordHasher->hashPassword($participant, '1234'));
        $participant->setSite($this->getReference(SiteFixtures::$campus[random_int(0, count(SiteFixtures::$campus) - 1)], Site::class));
        static::$participantKeys[0] = $participant->getUsername();
        $this->addReference(static::$participantKeys[0], $participant);
        $manager->persist($participant);

        ////////////////////////Participants///////////////////////////////
        for ($i = 1; $i < 10; $i++) {
            $participant = new Participant();
            $participant->setUsername($faker->userName);
            $participant->setNom($faker->lastName);
            $participant->setPrenom($faker->firstName);
            $participant->setTelephone($faker->phoneNumber);
            $participant->setMail($faker->email);
            $participant->setAdministrateur(false);
            $participant->setActif(true);
            $participant->setPassword($this->passwordHasher->hashPassword($participant, '1234'));
            $participant->setSite($this->getReference(SiteFixtures::$campus[random_int(0, count(SiteFixtures::$campus) - 1)], Site::class));
            static::$participantKeys[$i] = $participant->getUsername();
            $this->addReference(static::$participantKeys[$i], $participant);
            $manager->persist($participant);

        }

        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [SiteFixtures::class];
    }
}
