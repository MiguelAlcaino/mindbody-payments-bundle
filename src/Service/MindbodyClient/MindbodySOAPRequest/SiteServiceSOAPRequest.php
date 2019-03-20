<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

class SiteServiceSOAPRequest extends AbstractSOAPRequester
{
    const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/SiteService.asmx';

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