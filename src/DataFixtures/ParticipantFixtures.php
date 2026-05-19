<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {

        ////////////////////////Admin///////////////////////////////
           $participant = new Participant();
           $participant->setUsername('admin');
           $participant->setNom('ADMIN');
           $participant->setPrenom('Stephane');
           $participant->setTelephone(0000000000);
           $participant->setMail('admin@admin.test');
           $participant->setAdministrateur(true);
           $participant->setActif(true);
           $participant->setPassword($this->passwordHasher->hashPassword($participant, '1234'));
           $manager->persist($participant);
           $manager->flush();

        ////////////////////////Participant///////////////////////////////

           $participant = new Participant();
           $participant->setUsername('user1');
           $participant->setNom('USER1');
           $participant->setPrenom('Maurice');
           $participant->setTelephone(1111111111);
           $participant->setMail('user1@user.test');
           $participant->setAdministrateur(false);
           $participant->setActif(true);
           $participant->setPassword($this->passwordHasher->hashPassword($participant, '1234'));
           $manager->persist($participant);
           $manager->flush();
    }


}
