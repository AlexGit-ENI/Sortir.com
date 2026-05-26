<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Service\SortieService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    private $sortieService;
    public function __construct(ManagerRegistry $registry, SortieService $sortieService)
    {

        parent::__construct($registry, Sortie::class);
        $this->sortieService = $sortieService;
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

    public function findAllAndUpdate(): array {
        $sorties = $this->findAll();
        foreach ($sorties as $sortie) {
            $this->sortieService->updateEtatSortie($sortie);
            $this->sortieService->persistAndFlush($sortie);
        }
        return $sorties;
    }
}
