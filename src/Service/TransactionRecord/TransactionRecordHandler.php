<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\TransactionRecord;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Session\FromSessionService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\ShoppingCart\ShoppingCartService;

class TransactionRecordHandler
{
    /**
     * @var TransactionRecordFillerService
     */
    private $transactionRecordFillerService;

    /**
     * @var ShoppingCartService
     */
    private $shoppingCartService;

    /**
     * @var FromSessionService
     */
    private $fromSessionService;

    /**
     * TransactionRecordHandler constructor.
     *
     * @param TransactionRecordFillerService $transactionRecordFillerService
     * @param ShoppingCartService            $shoppingCartService
     * @param FromSessionService             $fromSessionService
     */
    public function __construct(
        TransactionRecordFillerService $transactionRecordFillerService,
        ShoppingCartService $shoppingCartService,
        FromSessionService $fromSessionService
    ) {
        $this->transactionRecordFillerService = $transactionRecordFillerService;
        $this->shoppingCartService            = $shoppingCartService;
        $this->fromSessionService             = $fromSessionService;
    }

    public function makePurchaseAndFillTransactionRecord(array $cartItems, array $paymentInfos): TransactionRecord
    {
        $transactionRecord = new TransactionRecord();
        $this->transactionRecordFillerService->fillTransactionRecord($transactionRecord);

        return $this->shoppingCartService->makePurchase(
            $transactionRecord,
            $this->fromSessionService->getMindbodyClientID(),
            $cartItems,
            $paymentInfos,
            $this->fromSessionService->getDiscountCodeUsed()
        );
    }
}