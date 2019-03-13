<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

class SaleServiceSOAPRequest
{
    const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/SaleService.asmx';
    /**
     * @var MindbodySOAPRequester
     */
    private $minbodySoapRequester;

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

        dump($response);

        return $response;
    }
}