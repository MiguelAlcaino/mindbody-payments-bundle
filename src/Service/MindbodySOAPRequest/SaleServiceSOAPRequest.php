<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\SaleService\CheckoutShoppingCartRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\SaleService\GetServicesRequest;

class SaleServiceSOAPRequest extends AbstractSOAPRequester
{
    const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/SaleService.asmx';

    /**
     * @param GetServicesRequest $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getServices(GetServicesRequest $request = null): array
    {
        $arrayRequest = $this->decodeRequesterObject($request);

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'GetServices',
            $arrayRequest
        );

        return $response;
    }

    /**
     * @param CheckoutShoppingCartRequest $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkoutShoppingCart(CheckoutShoppingCartRequest $request): array
    {
        $arrayRequest = $this->decodeRequesterObject($request);

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'CheckoutShoppingCart',
            $arrayRequest
        );

        return $response;
    }
}