<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\SiteServiceRequestHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MindbodyProgramService
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var SiteServiceRequestHandler
     */
    private $siteServiceRequestHandler;

    /**
     * MindbodyProgramService constructor.
     *
     * @param ParameterBagInterface     $parameterBag
     * @param SiteServiceRequestHandler $siteServiceRequestHandler
     */
    public function __construct(ParameterBagInterface $parameterBag, SiteServiceRequestHandler $siteServiceRequestHandler)
    {
        $this->parameterBag              = $parameterBag;
        $this->siteServiceRequestHandler = $siteServiceRequestHandler;
    }

    /**
     * @param string $classType
     *
     * @param bool   $useCache
     *
     * @return array
     */
    public function getProgramsIds($classType = 'class', $useCache = false){
        if (
            $classType === 'enrollment'
        ) {
            $programIds = [
                [
                    'id' => $this->parameterBag->get('enrollment_program_id'),
                ],
            ];
        } else {
            if($this->parameterBag->has('class_program_id')){
                $programIds = [
                    [
                        'id' => $this->parameterBag->get('class_program_id'),
                    ],
                ];
            }else{
                $programIds = $this->siteServiceRequestHandler->getPrograms($useCache);
            }

        }

        return $programIds;
    }
}