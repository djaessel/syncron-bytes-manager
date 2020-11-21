<?php

namespace App\Repository;

use App\Entity\UserActivation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * @method UserActivation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserActivation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserActivation[]    findAll()
 * @method UserActivation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserActivationRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, UserActivation::class);
    }

    // /**
    //  * @return UserActivation[] Returns an array of UserActivation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserActivation
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
