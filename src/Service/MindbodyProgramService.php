<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MindbodyProgramService
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * MindbodyProgramService constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param string $classType
     *
     * @return array
     */
    public function getProgramsIds($classType = 'class'){
        if (
            $classType === 'enrollment'
        ) {
            $programIds = [
                [
                    'id' => $this->parameterBag->get('enrollment_program_id'),
                ],
            ];
        } else {
            $programIds = [
                [
                    'id' => $this->parameterBag->get('class_program_id'),
                ],
            ];
        }

        return $programIds;
    }
}