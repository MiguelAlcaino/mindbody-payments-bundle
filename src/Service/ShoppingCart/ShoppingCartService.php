<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\ShoppingCart;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\LocationRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\ProductRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyRequestHandler\SaleServiceRequestHandler;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\ClassServiceSOAPRequester;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService\GetClassesRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\GetServicesRequest;

class ShoppingCartService
{
    /**
     * @var ClassServiceSOAPRequester
     */
    private $classServiceSOAPRequester;

    /**
     * @var SaleServiceRequestHandler
     */
    private $saleServiceRequestHandler;

    /**
     * @var LocationRepository
     */
    private $locationRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * ShoppingCartService constructor.
     *
     * @param ClassServiceSOAPRequester $classServiceSOAPRequester
     * @param SaleServiceRequestHandler $saleServiceRequestHandler
     * @param LocationRepository        $locationRepository
     * @param ProductRepository         $productRepository
     */
    public function __construct(
        ClassServiceSOAPRequester $classServiceSOAPRequester,
        SaleServiceRequestHandler $saleServiceRequestHandler,
        LocationRepository $locationRepository,
        ProductRepository $productRepository
    ) {
        $this->classServiceSOAPRequester = $classServiceSOAPRequester;
        $this->saleServiceRequestHandler = $saleServiceRequestHandler;
        $this->locationRepository        = $locationRepository;
        $this->productRepository         = $productRepository;
    }

    /**
     * @param int $classId
     *
     * @return Product[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFilteredServicesByClassId(int $classId): array
    {
        $getClassesRequest = (new GetClassesRequest())
            ->setClassIDs(
                [
                    $classId,
                ]
            );

        $getClassesResponse = $this->classServiceSOAPRequester->getClasses($getClassesRequest);
        $programId          = $getClassesResponse['GetClassesResult']['Classes']['Class']['ClassDescription']['Program']['ID'];
        $locationId         = $getClassesResponse['GetClassesResult']['Classes']['Class']['Location']['ID'];

        $getServicesRequest = new GetServicesRequest();
        $getServicesRequest->setProgramIDs(
            [
                $programId,
            ]
        )->setSellOnline(true);

        $services = $this->saleServiceRequestHandler->getFormattedServices($getServicesRequest);

        $servicesIds = array_map(
            function ($value) {
                return $value['id'];
            },
            $services
        );

        $dbLocation = $this->locationRepository->findBy(
            [
                'merchantId' => $locationId,
            ]
        );

        return $this->productRepository->getServicesByIdsAndLocations($servicesIds, $dbLocation);
    }
}