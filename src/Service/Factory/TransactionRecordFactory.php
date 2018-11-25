<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\Factory;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\PaymentGateway\Interfaces\Factory\TransactionRecordFactoryInterface;

class TransactionRecordFactory implements TransactionRecordFactoryInterface
{
    /**
     * @return TransactionRecord
     */
    public function create()
    {
        return new TransactionRecord();
    }
}