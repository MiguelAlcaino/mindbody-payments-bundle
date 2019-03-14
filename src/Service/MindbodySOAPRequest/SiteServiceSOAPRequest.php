<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

class SiteServiceSOAPRequest
{
    const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/SiteService.asmx';
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
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLocations(): array
    {
        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'GetLocations',
            [],
            false
        );

        return $response;
    }
}