<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService\AddClientToClassRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService\GetClassDescriptionsRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService\GetClassesRequest;

class ClassServiceSOAPRequester extends AbstractSOAPRequester
{
    private const SERVICE_URI = 'https://api.mindbodyonline.com/0_5_1/ClassService.asmx';

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

    /**
     * @param AddClientToClassRequest $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addClientToClass(AddClientToClassRequest $request): array
    {
        $arrayRequest = $this->decodeRequesterObject($request);

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'AddClientToClass',
            $arrayRequest
        );

        return $response;
    }

    /**
     * @param GetClassDescriptionsRequest|null $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClassDescriptions(GetClassDescriptionsRequest $request = null): array
    {
        $arrayRequest = $this->decodeRequesterObject($request);

        $response = $this->minbodySoapRequester->createEnvelopeAndExecuteRequest(
            self::SERVICE_URI,
            'GetClassDescriptions',
            $arrayRequest
        );

        return $response;
    }
}