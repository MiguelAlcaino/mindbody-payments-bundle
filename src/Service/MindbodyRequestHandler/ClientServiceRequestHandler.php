<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyRequestHandler;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\ClientServiceSOAPRequester;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\ClientService\GetClientServicesRequest;

class ClientServiceRequestHandler
{
    /**
     * @var ClientServiceSOAPRequester
     */
    private $clientServiceSOAPRequester;

    /**
     * ClientServiceRequestHandler constructor.
     *
     * @param ClientServiceSOAPRequester $clientServiceSOAPRequester
     */
    public function __construct(ClientServiceSOAPRequester $clientServiceSOAPRequester)
    {
        $this->clientServiceSOAPRequester = $clientServiceSOAPRequester;
    }

    /**
     * @param GetClientServicesRequest $getClientServicesRequest
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClientServicesFormatted(GetClientServicesRequest $getClientServicesRequest): array
    {
        $clientServices = $this->clientServiceSOAPRequester->getClientServices($getClientServicesRequest);

        $clientServicesFormatted = [];

        if (count($clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']) > 0) {
            if (
            array_key_exists(
                'Current',
                $clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']['ClientService']
            )
            ) {
                $clientService             = $clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']['ClientService'];
                $clientServicesFormatted[] = [
                    'current'        => $clientService['Current'],
                    'id'             => $clientService['ID'],
                    'name'           => $clientService['Name'],
                    'paymentDate'    => $clientService['PaymentDate'],
                    'activeDate'     => $clientService['ActiveDate'],
                    'expirationDate' => $clientService['ExpirationDate'],
                ];
            } else {
                foreach ($clientServices['GetClientServicesResponse']['GetClientServicesResult']['ClientServices']['ClientService'] as $clientService) {
                    $clientServicesFormatted[] = [
                        'current'        => $clientService['Current'],
                        'id'             => $clientService['ID'],
                        'name'           => $clientService['Name'],
                        'paymentDate'    => $clientService['PaymentDate'],
                        'activeDate'     => $clientService['ActiveDate'],
                        'expirationDate' => $clientService['ExpirationDate'],
                    ];
                }
            }
        }

        return $clientServicesFormatted;
    }

}