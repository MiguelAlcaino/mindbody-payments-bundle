<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\SaleService\GetClassesRequest;

class ClassServiceSOAPRequester extends AbstractSOAPRequester
{
    private const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/ClassService.asmx';

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
     * @param GetClassesRequest $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClasses(GetClassesRequest $request): array
    {
        $arrayRequest = $this->decodeRequesterObject($request);

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'GetClasses',
            $arrayRequest
        );

        return $response;
    }
}