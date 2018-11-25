<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Model\MindbodySession;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FromSessionService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * FromSessionService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SessionInterface       $session
     */
    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session       = $session;
    }

    /**
     * @return Customer|null
     */
    public function getCustomerFromSession()
    {
        $clientId = $this->session->get(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME);

        return $this->entityManager->getRepository(Customer::class)->findOneBy(
            [
                'merchantId' => $clientId,
            ]
        );
    }

    /**
     * @param TransactionRecord $transactionRecord
     *
     * @return TransactionRecord
     */
    public function fillTransactionRecord(TransactionRecord $transactionRecord)
    {
        $customer = $this->getCustomerFromSession();
        $transactionRecord->setCustomer($customer)
            ->setUserCountry($customer->getUserCountry());

        return $transactionRecord;
    }
}