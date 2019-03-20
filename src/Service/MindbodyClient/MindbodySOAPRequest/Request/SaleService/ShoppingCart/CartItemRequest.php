<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\ShoppingCart;

class CartItemRequest implements \JsonSerializable
{
    /**
     * @var ItemRequest
     */
    private $Item;

    /**
     * @var int
     */
    private $Quantity;

    /**
     * @var float
     */
    private $DiscountAmount;

    /**
     * CartItemRequest constructor.
     *
     * @param ItemRequest $Item
     * @param int         $Quantity
     * @param float       $DiscountAmount
     */
    public function __construct(ItemRequest $Item, int $Quantity, float $DiscountAmount = 0)
    {
        $this->Item           = $Item;
        $this->Quantity       = $Quantity;
        $this->DiscountAmount = $DiscountAmount;
    }

    public function jsonSerialize()
    {
        return [
            'Item' => $this->Item,
            'Quantity' => $this->Quantity,
            'DiscountAmount' => $this->DiscountAmount
        ];
    }

    /**
     * @return ItemRequest
     */
    public function getItem(): ItemRequest
    {
        return $this->Item;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->Quantity;
    }

    /**
     * @return float
     */
    public function getDiscountAmount(): float
    {
        return $this->DiscountAmount;
    }
}