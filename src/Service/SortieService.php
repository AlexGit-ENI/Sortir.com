<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Enum\EtatSortie;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;


class SortieService
{

    private $entityManager;
    private $participantRepository;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Exception
     */
    public function create(Sortie $sortie, Participant $participant){


        $dateDuJour = new \DateTime();
        $datePlusUnAn = new \DateTime();
        $datePlusUnAn ->modify('+1 year');
        $dateDebut = $sortie->getDateHeureDebut();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();


        $sortie->setOrganisateur($participant);
        $sortie->setSite($participant->getSite());
        $sortie->setEtatSortie(EtatSortie::OPEN);
//        $organisateur = $this->getUser();
//        $user = $sortie->getOrganisateur()->getUserIdentifier();

//        $sortie->setOrganisateur()

        if($dateDuJour > $dateLimiteInscription){
            throw new Exception("La date de limite d'inscription ne peut pas être dans le passé");
        }

        if($dateDuJour > $dateDebut){
            throw new Exception("Impossible de créer une sortie dans le passé");
        }

        if($dateLimiteInscription > $dateDebut){
            throw new Exception("Erreur de configuration des dates de début et de limite d'inscription à la sortie");
        }

        if($dateDebut>$datePlusUnAn){
            throw new Exception("La date de début de l'évenement est trop lointaine. Maximum: 1 an");
        }




        $this->entityManager->persist($sortie);

        $this->entityManager->flush();
    }

    public function updateEtatSortie(Sortie $sortie): Sortie{

        $dateDuJour = new \DateTime();
        $dateDebut = $sortie->getDateHeureDebut();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();

        // Avoir le DateTime de la fin de la sortie
        $dateFinSortie = $dateDebut;
        $dureeSortie = $sortie->getDuree();
        $dateFinSortie->modify('+'.$dureeSortie.' minute');

        // Avoir le DateTime d'un mois plus tard après le début de la sortie
        $datePlusUnMois = $dateDebut;
        $datePlusUnMois->modify('+1 months');

        if ($sortie->getEtatSortie() == null) {
            $sortie->setEtatSortie(EtatSortie::CREATED);
            return $sortie;
        }

        if ($sortie->getEtatSortie() == EtatSortie::ARCHIVED) {
            return $sortie;
        }

        if ($sortie->getEtatSortie() == EtatSortie::CANCELLED) {
            if ($dateDuJour == $datePlusUnMois) {
                $sortie->setEtatSortie(EtatSortie::ARCHIVED);
                return $sortie;
            }
            return $sortie;
        }

        if ($dateDebut)

        return $sortie;
    }

}
