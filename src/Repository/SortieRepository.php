<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Service\SortieService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
//use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
//    private $sortieService;
    public function __construct(ManagerRegistry $registry)
    {
//
        parent::__construct($registry, Sortie::class);
//        $this->sortieService = $sortieService;
    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findBySite(int $id)
    {
        $entityManager = $this->getEntityManager();
        $querybuilder = $this->createQueryBuilder("sortie");
        $querybuilder->leftJoin("sortie.site", "site");
        $querybuilder->andWhere("site.id = :id");
        $querybuilder->setParameter("site", $id);
        $querybuilder->addSelect("site");
        $query = $querybuilder->getQuery();

        return $query->getResult();
    }

//    public function findAllAndUpdate(): array {
//        $sorties = $this->findAll();
//        foreach ($sorties as $sortie) {
//            $this->sortieService->updateEtatSortie($sortie);
//            $this->sortieService->persistAndFlush($sortie);
//        }
//        return $sorties;
//    }

    public function searchSortieByTerme(string $termeRecherche){
        $querybuilder = $this->createQueryBuilder("sortie");
        $querybuilder->leftJoin("sortie.site", "site");
        $querybuilder->leftJoin("sortie.organisateur", "organisateur");
        $querybuilder->leftJoin("sortie.lieu", "lieu");
        $querybuilder->leftJoin("lieu.ville", "ville");
        $querybuilder->andWhere(
            $querybuilder->expr()->orX(
                $querybuilder->expr()->like("sortie.nom", ":termeRecherche"),
                $querybuilder->expr()->like("sortie.description", ":termeRecherche"),
                $querybuilder->expr()->like("lieu.nom", ":termeRecherche"),
                $querybuilder->expr()->like("ville.nom", ":termeRecherche"),
            )
        );
        $querybuilder->setParameter("termeRecherche", '%'.$termeRecherche.'%');
        $query = $querybuilder->getQuery();
        return $query->getResult();
    }

    public function searchSortiesByDate(string $dateMin, string $dateMax)
    {
        $querybuilder = $this->createQueryBuilder("sortie");
        $querybuilder->andWhere(
            $querybuilder->expr()->between("sortie.dateHeureDebut", ":dateMin", ":dateMax")
        );
        $querybuilder->setParameter("dateMin", $dateMin);
        $querybuilder->setParameter("dateMax", $dateMax);
        $query = $querybuilder->getQuery();
        return $query->getResult();
    }

    public function findSortiesBeforeDate(\DateTime $date){
        $querybuilder = $this->createQueryBuilder("sortie");
        $querybuilder->andWhere('sortie.dateHeureDebut < :date');
        $querybuilder->setParameter('date', $date);
        $querybuilder->orderBy('sortie.dateHeureDebut', 'DESC');
        $query = $querybuilder->getQuery();
        return $query->getResult();
    }
}
