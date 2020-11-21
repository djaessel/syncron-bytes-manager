<?php

namespace App\Repository;

use App\Entity\TransferData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * @method TransferData|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferData|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferData[]    findAll()
 * @method TransferData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferDataRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, TransferData::class);
    }

    // /**
    //  * @return TransferData[] Returns an array of TransferData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TransferData
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
