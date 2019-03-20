<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClientService\GetClientServicesRequest;

class ClientServiceSOAPRequester extends AbstractSOAPRequester
{
    private const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/ClientService.asmx';

    /**
     * @param GetClientServicesRequest $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClientServices(GetClientServicesRequest $request): array
    {
        $arrayRequest = $this->decodeRequesterObject($request);

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'GetClientServices',
            $arrayRequest
        );

        return $response;
    }
}