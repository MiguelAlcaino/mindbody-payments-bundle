<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 14/03/18
 * Time: 22:57
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Repository;


use Doctrine\ORM\EntityRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount;

class CustomerDiscountRepository extends EntityRepository
{
    /**
     * @param Customer $customer
     * @param string $code
     * @param \DateTime $now
     * @return CustomerDiscount|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getValidCustomerDiscount(Customer $customer, string $code, \DateTime $now)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('cd, d, pd, p')
            ->from(CustomerDiscount::class, 'cd')
            ->leftJoin('cd.discount', 'd')
            ->leftJoin('d.productDiscounts', 'pd')
            ->leftJoin('pd.product', 'p')
            ->where('cd.customer = :customer')
            ->andWhere('cd.code = :code')
            ->andWhere('cd.isUsed = false')
            ->andWhere('cd.validFrom < :now AND cd.validUntil > :now')
            ->andWhere('pd.active = true')
            ->setParameters([
                'code' => $code,
                'customer' => $customer,
                'now' => $now
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param Customer $customer
     * @param \DateTime $now
     * @return CustomerDiscount[]
     */
    public function getValidCustomerDiscounts(Customer $customer, \DateTime $now)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('cd')
            ->from(CustomerDiscount::class, 'cd')
            ->leftJoin('cd.discount', 'd')
            ->where('cd.customer = :customer')
            ->andWhere('d.enabled = true')
            ->andWhere('cd.validFrom < :now AND cd.validUntil > :now')
            ->setParameters([
                'customer' => $customer,
                'now' => $now
            ])
            ->getQuery();

        return $query->getResult();
    }
}