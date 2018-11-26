<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\TransactionRecord;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\FromSessionService;

class TransactionRecordFillerService
{
    /**
     * @var FromSessionService
     */
    private $fromSessionService;

    /**
     * TransactionRecordFillerService constructor.
     *
     * @param FromSessionService $fromSessionService
     */
    public function __construct(FromSessionService $fromSessionService)
    {
        $this->fromSessionService = $fromSessionService;
    }

    public function fillDiscountData(TransactionRecord $transactionRecord)
    {
        $transactionRecord
            ->setDiscountCode($this->fromSessionService->getDiscountCodeUsed())
            ->setDiscountAmount($this->fromSessionService->getDiscountAmount());

        return $transactionRecord;
    }

    public function fillCustomerData(TransactionRecord $transactionRecord)
    {
        $customer = $this->fromSessionService->getCustomerFromSession();
        $transactionRecord
            ->setCustomer($customer)
            ->setUserCountry($customer->getUserCountry())
            ->setUserCity($customer->getUserCity())
            ->setDocumentType($customer->getDocumentType())
            ->setDocumentNumber($customer->getDocumentNumber())
            ->setUserCountry($customer->getUserCountry());

        return $transactionRecord;
    }

    public function fillCreditCardInformationData(TransactionRecord $transactionRecord)
    {
        $transactionRecord
            ->setCreditCardHolderName($this->fromSessionService->getCreditCardHolderName())
            ->setCreditCardLastFourDigits($this->fromSessionService->getCreditCardLastFourDigits());
    }

    /**
     * @param TransactionRecord $transactionRecord
     *
     * @return TransactionRecord
     */
    public function fillTransactionRecord(TransactionRecord $transactionRecord)
    {
        $transactionRecord
            ->setMindbodyPaymentMethodId($this->fromSessionService->getSelectedMindbodyPaymentMethodId())
            ->setServiceId($this->fromSessionService->getSelectedMindbodyServiceId());

        $this->fillDiscountData($transactionRecord);
        $this->fillCustomerData($transactionRecord);
        $this->fillCreditCardInformationData($transactionRecord);

        return $transactionRecord;
    }
}