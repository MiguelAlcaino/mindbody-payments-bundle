<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

class SaleServiceSOAPRequest extends AbstractSOAPRequester
{
    const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/SaleService.asmx';

    /**
     * @param array  $programs
     * @param string $classScheduleId
     * @param string $locationId
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getServices(array $programs = [], string $classScheduleId = '', string $locationId = ''): array
    {
        $request = [];

        if (!empty($programs)) {
            $request['ProgramIDs'] = [
                'int' => $programs,
            ];
        }

        if (!empty($classScheduleId)) {
            $request['ClassScheduleID'] = $classScheduleId;
        }

        if(!empty($locationId)){
            $request['LocationID'] = $locationId;
        }

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'GetServices',
            $request
        );

        return $response;
    }
}