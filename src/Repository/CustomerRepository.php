<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerSubscription;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * CustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 * @method Customer findOneBy(array $criteria, array $orderBy = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function getLastCustomer()
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Customer::class, 'c')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getCurrentCustomersOfProduct(Product $product)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(Customer::class, 'c')
            ->leftJoin('c.transactionRecords', 'tr')
            ->leftJoin('tr.transactionItems', 'ti')
            ->where('tr.serviceExpirationDate < :now')
            ->andWhere('ti.merchantId = :product_merchant_id')
            ->setParameter('now', new \DateTime())
            ->setParameter('product_merchant_id', $product->getMerchantId())
            ->groupBy('c.id')
            ->getQuery();

        return $query->getResult();
    }

    public function getAllBy($criteria = [], $limit = 50, $offset = 0, $returnArray = false)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('c, count(tr.id) as countTransactionRecords')
            ->from(Customer::class, 'c')
            ->leftJoin('c.transactionRecords', 'tr')
            ->addOrderBy('countTransactionRecords', 'DESC')
            ->groupBy('c.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (array_key_exists('customerName', $criteria)) {
            $query->where('c.firstName LIKE :search')
                ->orWhere('c.lastName LIKE :search')
                ->setParameter('search', '%' . $criteria['customerName'] . '%');
        }

        if (array_key_exists('customerName', $criteria) && array_key_exists('start', $criteria)) {
            $query->andWhere('t.created >= :start AND t.created <= :end')
                ->setParameter('start', $criteria['start'])
                ->setParameter('end', $criteria['end']);
        } elseif (array_key_exists('start', $criteria) && !array_key_exists('customerName', $criteria)) {
            $query->where('t.created >= :start AND t.created <= :end')
                ->setParameter('start', $criteria['start'])
                ->setParameter('end', $criteria['end']);
        }

        return $returnArray ? $query->getQuery()->getArrayResult() : $query->getQuery()->getResult();
    }

    public function countBy($criteria)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from(Customer::class, 'c');

        if (array_key_exists('customerName', $criteria)) {
            $query->where('c.firstName LIKE :search')
                ->orWhere('c.lastName LIKE :search')
                ->setParameter('search', '%' . $criteria['customerName'] . '%');
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Product $product
     * @param \DateTime $dateTime
     * @param boolean $returnArray
     * @return Customer[]
     */
    public function getAllWhoPurchasedProductAndExpiryDate(Product $product, \DateTime $dateTime, $returnArray = false)
    {
        $rawQuery = '
            SELECT 
                c.*
            FROM
                transaction_record tr
            LEFT JOIN customer c ON c.id = tr.customer_id
            LEFT JOIN transaction_item ti ON ti.transaction_record_id = tr.id
            WHERE
                tr.service_expiration_date = :expiration_date
                AND ti.merchantId = :product_id
            GROUP BY c.id
        ';

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Customer::class, 'c')
            ->addFieldResult('c', 'id', 'id')
            ->addFieldResult('c', 'firstName', 'firstName')
            ->addFieldResult('c', 'lastName', 'lastName')
            ->addFieldResult('c', 'email', 'email');

        $query = $this
            ->getEntityManager()
            ->createNativeQuery($rawQuery, $rsm);

        $query
            ->setParameter('expiration_date', $dateTime)
            ->setParameter('product_id', $product->getMerchantId());

        return $returnArray ? $query->getArrayResult() : $query->getResult();
    }

    /**
     * @param Customer $customer
     * @return CustomerSubscription[]
     */
    public function getActiveSubscriptions(Customer $customer)
    {
        $now   = new \DateTime();
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('cs, t')
            ->from(CustomerSubscription::class, 'cs')
            ->leftJoin('cs.transactionRecords', 't')
            ->where('t.serviceActivationDate < :now')
            ->andWhere('t.serviceExpirationDate > :now')
            ->andWhere('cs.status = :status_active OR cs.status = :status_user_cancelled')
            ->andWhere('cs.customer = :customer')
            ->orderBy('t.created', 'DESC')
            ->setParameter('customer', $customer)
            ->setParameter('now', $now)
            ->setParameter('status_active', CustomerSubscription::STATUS_ACTIVE)
            ->setParameter('status_user_cancelled', CustomerSubscription::STATUS_USER_CANCELLED)
            ->getQuery();

        return $query->getResult();
    }
}
