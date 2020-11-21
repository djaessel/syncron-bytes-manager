<?php

namespace App\Repository;

use App\Entity\TransferFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * @method TransferFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferFile[]    findAll()
 * @method TransferFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferFileRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, TransferFile::class);
    }

    // /**
    //  * @return TransferFile[] Returns an array of TransferFile objects
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
    public function findOneBySomeField($value): ?TransferFile
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
