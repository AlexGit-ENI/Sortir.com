<?php

namespace App\Services;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;

class SortieService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function createSortie(Sortie $sortie) {

        $dateDebut = $sortie->getDateHeureDebut();
        $dateFinInscription = $sortie->getDateLimiteInscription();
        $site = $sortie->getSite();
        $organisateur = $sortie->getOrganisateur();

        if($dateFinInscription < $dateDebut) {
            if($site != null && $organisateur != null) {
                $this->entityManager->persist($sortie);
                $this->entityManager->flush();
            }
        }

    }


}
