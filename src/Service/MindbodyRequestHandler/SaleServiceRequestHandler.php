<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyRequestHandler;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\SaleServiceSOAPRequest;

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
     * @param array  $programs
     * @param string $classScheduleId
     * @param string $locationId
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFormattedServices(array $programs = [], string $classScheduleId = '', string $locationId = ''): array
    {
        $services = $this->saleServiceSOAPRequest->getServices($programs, $classScheduleId, $locationId);

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
}