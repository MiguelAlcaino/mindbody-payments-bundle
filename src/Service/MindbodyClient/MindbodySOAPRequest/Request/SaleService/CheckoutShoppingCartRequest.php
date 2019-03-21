<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\ShoppingCart\CartItemRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\ShoppingCart\PaymentInfoRequest;

class CheckoutShoppingCartRequest implements \JsonSerializable
{
    /**
     * @var string
     */
    private $ClientID;

    /**
     * @var CartItemRequest[]
     */
    private $CartItems;

    /**
     * @var PaymentInfoRequest[]
     */
    private $Payments;

    /**
     * @var bool
     */
    private $InStore;

    /**
     * @var bool
     */
    private $Test;

    /**
     * @var array
     */
    private $Fields;

    /**
     * @var string
     */
    private $PromotionCode;

    /**
     * CheckoutShoppingCartRequest constructor.
     *
     * @param string $ClientID
     * @param array  $CartItems
     * @param bool   $Test
     */
    public function __construct(string $ClientID, array $CartItems, bool $Test = true)
    {
        $this->ClientID  = $ClientID;
        $this->CartItems = $CartItems;
        $this->Test      = $Test;
        $this->InStore   = false;
    }

    public function jsonSerialize()
    {
        $jsonArray = [
            'ClientID'  => $this->ClientID,
            'CartItems' => [
                'CartItem' => $this->CartItems,
            ],
            'Test'      => $this->Test,
            'InStore'   => $this->InStore,
        ];

        if ($this->Payments !== null) {
            $jsonArray['Payments'] = [
                'PaymentInfo' => $this->Payments,
            ];
        }

        if ($this->PromotionCode !== null) {
            $jsonArray['PromotionCode'] = $this->PromotionCode;
        }

        if ($this->Fields !== null) {
            $jsonArray['Fields'] = [
                'string' => $this->Fields,
            ];
        }

        return $jsonArray;
    }

    /**
     * @return bool
     */
    public function isInStore(): bool
    {
        return $this->InStore;
    }

    /**
     * @param bool $InStore
     *
     * @return CheckoutShoppingCartRequest
     */
    public function setInStore(bool $InStore): CheckoutShoppingCartRequest
    {
        $this->InStore = $InStore;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): ?array
    {
        return $this->Fields;
    }

    /**
     * @param array $Fields
     *
     * @return CheckoutShoppingCartRequest
     */
    public function setFields(array $Fields): CheckoutShoppingCartRequest
    {
        $this->Fields = $Fields;

        return $this;
    }

    /**
     * @return string
     */
    public function getPromotionCode(): ?string
    {
        return $this->PromotionCode;
    }

    /**
     * @param string $PromotionCode
     *
     * @return CheckoutShoppingCartRequest
     */
    public function setPromotionCode(string $PromotionCode): CheckoutShoppingCartRequest
    {
        $this->PromotionCode = $PromotionCode;

        return $this;
    }

    /**
     * @return PaymentInfoRequest[]
     */
    public function getPayments(): ?array
    {
        return $this->Payments;
    }

    /**
     * @param PaymentInfoRequest[] $Payments
     *
     * @return CheckoutShoppingCartRequest
     */
    public function setPayments(array $Payments): CheckoutShoppingCartRequest
    {
        $this->Payments = $Payments;

        return $this;
    }
}