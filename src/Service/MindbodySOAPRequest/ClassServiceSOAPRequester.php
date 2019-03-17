<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

class ClassServiceSOAPRequester
{
    const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/ClassService.asmx';
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

    public function getClasses(): array
    {
        $request = [];

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'GetClasses',
            $request
        );

        return $response;
    }
}