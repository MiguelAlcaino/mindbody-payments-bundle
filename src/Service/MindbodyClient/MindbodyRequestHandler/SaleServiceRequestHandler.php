<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodyRequestHandler;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\CheckoutShoppingCartRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\GetServicesRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\ShoppingCart\CartItemRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\ShoppingCart\PaymentInfoRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\SaleServiceSOAPRequest;

class SaleServiceRequestHandler
{
    /**
     * @var SaleServiceSOAPRequest
     */
    private $saleServiceSOAPRequest;

    /**
     * SaleServiceRequestHandler constructor.
     *
     * @param SaleServiceSOAPRequest $saleServiceSOAPRequest
     */
    public function __construct(SaleServiceSOAPRequest $saleServiceSOAPRequest)
    {
        $this->saleServiceSOAPRequest = $saleServiceSOAPRequest;
    }

    /**
     * @param GetServicesRequest $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFormattedServices(GetServicesRequest $request = null): array
    {
        $services = $this->saleServiceSOAPRequest->getServices($request);

        $formattedServices = [];
        foreach ($services['GetServicesResult']['Services']['Service'] as $service) {
            $formattedServices[] = [
                'name'  => $service['Name'],
                'price' => $service['OnlinePrice'],
                'id'    => $service['ID'],
            ];
        }

        usort(
            $formattedServices,
            function ($a, $b) {
                $pos_a = $a['price'];
                $pos_b = $b['price'];

                return $pos_a - $pos_b;
            }
        );

        return $formattedServices;
    }

    /**
     * @param string            $clientId
     * @param CartItemRequest[] $cartItems
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculateShoppingCart(string $clientId, array $cartItems): array
    {
        $checkoutShoppingCartRequest = new CheckoutShoppingCartRequest(
            $clientId,
            $cartItems,
            true
        );
        $checkoutShoppingCartRequest
            ->setInStore(true)
            ->setFields(['paymentcheck']);

        return $this->saleServiceSOAPRequest->checkoutShoppingCart($checkoutShoppingCartRequest);
    }

    /**
     * Returns an array of formatted custom payment methods
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFormattedCustomPaymentMethods(): array
    {
        $customPaymentMethods = $this->saleServiceSOAPRequest->getCustomPaymentMethods();
        dump($customPaymentMethods);
        $formattedCustomPaymentMethods = [];

        if ((int)$customPaymentMethods['GetCustomPaymentMethodsResult']['ResultCount'] === 1) {
            $customPaymentMethod                                         = $customPaymentMethods['GetCustomPaymentMethodsResult']['PaymentMethods']['CustomPaymentInfo'];
            $formattedCustomPaymentMethods[$customPaymentMethod['Name']] = $customPaymentMethod['ID'];
        } else {
            foreach ($customPaymentMethods['GetCustomPaymentMethodsResult']['PaymentMethods']['CustomPaymentInfo'] as $customPaymentMethod) {
                $formattedCustomPaymentMethods[$customPaymentMethod['Name']] = $customPaymentMethod['ID'];
            }
        }

        return $formattedCustomPaymentMethods;
    }

    /**
     * @param string               $clientId
     * @param CartItemRequest[]    $cartItems
     * @param PaymentInfoRequest[] $paymentInfos
     * @param string|null          $promotionalCode
     * @param bool                 $test
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function purchaseShoppingCart(
        string $clientId,
        array $cartItems,
        array $paymentInfos,
        string $promotionalCode = null,
        bool $test = true
    ): array {
        $checkoutShoppingCartRequest = new CheckoutShoppingCartRequest(
            $clientId,
            $cartItems,
            $test
        );
        $checkoutShoppingCartRequest
            ->setPayments($paymentInfos)
            ->setPromotionCode($promotionalCode);

        return $this->saleServiceSOAPRequest->checkoutShoppingCart($checkoutShoppingCartRequest);
    }
}