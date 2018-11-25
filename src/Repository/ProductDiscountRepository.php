<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 12/03/18
 * Time: 01:08
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Repository;


use MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount;
use Doctrine\ORM\EntityRepository;


class ProductDiscountRepository extends EntityRepository
{
    /**
     * @param $discount
     * @param $productIds
     * @return array|ProductDiscount[]
     */
    public function getOtherProductsOfDiscount($discount, $productIds)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('pd')
            ->from(ProductDiscount::class, 'pd')
            ->where('pd.discount = :discount')
            ->setParameter('discount', $discount)
            ->andWhere('pd.product NOT IN (:product_ids)')
            ->setParameter('product_ids', $productIds)
            ->getQuery();

        return $query->getResult();
    }
}