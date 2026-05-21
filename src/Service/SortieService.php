<?php

namespace App\Service;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Exception;


class SortieService
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Exception
     */
    public function create(Sortie $sortie){
        $today = new \DateTime();
        $dateDebut = $sortie->getDateHeureDebut();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();


        if($today > $dateLimiteInscription){
            throw new Exception("La date de limite d'inscription ne peut pas être dans le passé");
        }

        if($today > $dateDebut){
            throw new Exception("Impossible de créer une sortie dans le passé");
        }

        if($dateLimiteInscription > $dateDebut){
            throw new Exception("Erreur de configuration des dates de début et de limite d'inscription à la sortie");
        }

        if($dateDebut>$today->add(new \DateInterval('P1Y'))->format("Y-m-d")){
            throw new Exception("La date de début de l'évenement est trop lointaine. Maximum: 1 an");
        }




        $this->entityManager->persist($sortie);

        $this->entityManager->flush();
    }

}
