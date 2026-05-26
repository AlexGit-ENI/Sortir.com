<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Enum\EtatSortie;
use App\Repository\ParticipantRepository;
use DateTimeZone;
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


        $dateDuJour = new \DateTime('now', new DateTimeZone('Europe/Paris'));
        $datePlusUnAn = new \DateTime('now', new DateTimeZone('Europe/Paris'));
        $datePlusUnAn ->modify('+1 year');
        $dateDebut = $sortie->getDateHeureDebut();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();


        $sortie->setOrganisateur($participant);
        $sortie->setSite($participant->getSite());
        $sortie->setEtatSortie(EtatSortie::CREATED);
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

    public function persistAndFlush(Sortie $sortie): void
    {
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    public function updateEtatSortie(Sortie $sortie): Sortie{

        $dateDuJour = new \DateTime('now', new DateTimeZone('Europe/Paris'));
        $dateDebut = $sortie->getDateHeureDebut();

        $dateFinInscription = $sortie->getDateLimiteInscription();

        $nbInscrits = count($sortie->getListeParticipants());
        $nbInscritsMax = $sortie->getNbInscriptionsMax();

        // Avoir le DateTime de la fin de la sortie
        // clone indispensable sinon nous récupérons l'adresse mémoire
        $dateFinSortie = clone $dateDebut;
        $dureeSortie = $sortie->getDuree();
        $dateFinSortie->modify('+'.$dureeSortie.' minute');

        // Avoir le DateTime d'un mois plus tard après le début de la sortie
        // clone indispensable sinon nous récupérons l'adresse mémoire
        $datePlusUnMois = clone $dateDebut;
        $datePlusUnMois->modify('+1 months');

        // Ici, l'odre des IF est important

        if ($sortie->getEtatSortie() == EtatSortie::CREATED) {
            return $sortie;
        }

        // Une sortie archivée ne peut pas changer d'état
        if ($sortie->getEtatSortie() == EtatSortie::ARCHIVED) {
            return $sortie;
        }

        // Un mois après la fin d'une sortie, elle devient archivée
        if ($dateDuJour >= $datePlusUnMois) {
            $sortie->setEtatSortie(EtatSortie::ARCHIVED);
            return $sortie;
        }
        if ($sortie->getEtatSortie() === EtatSortie::CANCELLED) {
            return $sortie;
        }
        //dd($dateDuJour, $dateFinSortie);
        // Si une sortie passe sa date de fin, elle devient Passée
        if ($dateDuJour>$dateFinSortie) {

            $sortie->setEtatSortie(EtatSortie::PAST);
            return $sortie;
        }

        // Si une sortie est encours, elle devient En cours
        if ($dateDebut>=$dateDuJour && $dateFinSortie<=$dateDuJour) {
            $sortie->setEtatSortie(EtatSortie::CURRENT);
            return $sortie;
        }

        // Si une sortie est complete ou bien que le delais est dépassé, elle devient Cloturée
        if($nbInscrits>=$nbInscritsMax || $dateDuJour>=$dateFinInscription ){
            $sortie->setEtatSortie(EtatSortie::CLOSED);
            return $sortie;
        }

        // Dans tous les autres cas, la sortie devient Ouverte
        $sortie->setEtatSortie(EtatSortie::OPEN);

        return $sortie;
    }


    public function updateSortie(Sortie $sortie): Sortie {
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
        return $sortie;
    }

}

