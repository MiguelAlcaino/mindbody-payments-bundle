<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Repository;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CustomSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomSetting[]    findAll()
 * @method CustomSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomSettingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomSetting::class);
    }

    // /**
    //  * @return CustomSetting[] Returns an array of CustomSetting objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CustomSetting
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
