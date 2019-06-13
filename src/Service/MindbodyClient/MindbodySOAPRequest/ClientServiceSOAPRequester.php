<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClientService\GetClientServicesRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClientService\ValidateLoginRequest;

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

    /**
     * @param ValidateLoginRequest $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validateLogin(ValidateLoginRequest $request): array
    {
        $arrayRequest = $this->decodeRequesterObject($request);

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'ValidateLogin',
            $arrayRequest
        );

        return $response;
    }
}
