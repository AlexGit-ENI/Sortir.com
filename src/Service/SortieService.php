<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Enum\EtatSortie;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;


class SortieService
{

    private $sortieRepository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, SortieRepository $sortieRepository){
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
    }

    /**
     * @throws Exception
     */
    public function create(Sortie $sortie, Participant $participant){


        $dateDuJour = new \DateTime('now', new DateTimeZone('Europe/Paris'));
        $datePlusUnAn = new \DateTime('now', new DateTimeZone('Europe/Paris'));
        $datePlusUnAn ->modify('+1 year');
        $dateDebut = $sortie->getDateHeureDebut()->setTimezone(new DateTimeZone('Europe/Paris'));
        $dateLimiteInscription = $sortie->getDateLimiteInscription()->setTimezone(new DateTimeZone('Europe/Paris'));

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
    public function findAllAndUpdate(): array {
        $sorties = $this->sortieRepository->findBy(
            [],
            ['dateHeureDebut' => 'DESC'],
        );
        foreach ($sorties as $sortie) {
            $this->updateEtatSortie($sortie);
            $this->persistAndFlush($sortie);
        }
        return $sorties;
    }



    public function updateEtatSortie(Sortie $sortie): Sortie{

        $dateDuJour = new \DateTime('now', new DateTimeZone('Europe/Paris'));
        $dateDebut = $sortie->getDateHeureDebut();
        date_timezone_set($dateDebut, timezone_open('Europe/Paris'));

        $dateFinInscription = $sortie->getDateLimiteInscription();
        date_timezone_set($dateFinInscription, timezone_open('Europe/Paris'));

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

        // Seul l'organisateur peut rendre une sortie OPEN. Dans tous les cas, si la sortie est CREATED, elle reste ainsi.
        if ($sortie->getEtatSortie() == EtatSortie::CREATED) {
            return $sortie;
        }

        // Une sortie ARCHIVED ne peut pas changer d'état
        if ($sortie->getEtatSortie() == EtatSortie::ARCHIVED) {
            return $sortie;
        }

        // Un mois après la fin d'une sortie, elle devient ARCHIVED
        if ($dateDuJour >= $datePlusUnMois) {
            $sortie->setEtatSortie(EtatSortie::ARCHIVED);
            return $sortie;
        }

        if ($sortie->getEtatSortie() === EtatSortie::CANCELLED) {
            return $sortie;
        }

        // Si une sortie passe sa date de fin, elle devient PAST
        if ($dateDuJour>$dateFinSortie) {
            $sortie->setEtatSortie(EtatSortie::PAST);
            return $sortie;
        }

        // Si une sortie est en cours, elle devient CURRENT
        if ($dateDebut>=$dateDuJour && $dateFinSortie<=$dateDuJour) {
            $sortie->setEtatSortie(EtatSortie::CURRENT);
            return $sortie;
        }

//        if ($sortie->getId() == 161) {
//            $isBigger = $dateDuJour>$dateFinInscription;
//            dd('date du jour', $dateDuJour, 'est plus grand que', 'date fin inscription', $dateFinInscription, ': ', $isBigger);
//        }

        // Une sortie est CLOSED lorsqu'elle la date du jour dépasse la date limite d'inscription ou bien que le nb max d'inscrit est atteint
        if( $nbInscrits >= $nbInscritsMax || $dateDuJour>$dateFinInscription ){
            $sortie->setEtatSortie(EtatSortie::CLOSED);
            return $sortie;
        }

        // Dans tous les autres cas, l'état de la sortie devient OPEN
        $sortie->setEtatSortie(EtatSortie::OPEN);
        return $sortie;
    }

    public function searchSorties(string $termeRecherche): array
    {
        return $this->sortieRepository->searchSortieByTerme(trim(strtolower($termeRecherche)));
    }

    public function filterSortiesByDate(string $dateMin, string $dateMax): array
    {
        return $this->sortieRepository->searchSortiesByDate($dateMin, $dateMax);
    }
}

