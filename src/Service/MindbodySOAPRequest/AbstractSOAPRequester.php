<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

abstract class AbstractSOAPRequester
{
    /**
     * @var MindbodySOAPRequester
     */
    protected $minbodySoapRequester;

    /**
     * SaleServiceSOAPRequest constructor.
     *
     * @param MindbodySOAPRequester $minbodySoapRequester
     */
    public function __construct(MindbodySOAPRequester $minbodySoapRequester)
    {
        $this->minbodySoapRequester = $minbodySoapRequester;
    }

    /**
     * @param mixed $requesterObject
     *
     * @return array
     */
    public function decodeRequesterObject($requesterObject): array
    {
        return $requesterObject === null ? [] : json_decode(json_encode($requesterObject), true);
    }
}