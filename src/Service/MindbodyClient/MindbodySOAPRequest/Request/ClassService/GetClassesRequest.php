<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService;

class GetClassesRequest implements \JsonSerializable
{
    /**
     * @var array
     */
    private $ClassIDs;

    public function jsonSerialize()
    {
        return [
            'ClassIDs' => [
                'int' => $this->ClassIDs
            ]
        ];
    }

    /**
     * @return array
     */
    public function getClassIDs(): array
    {
        return $this->ClassIDs;
    }

    /**
     * @param array $ClassIDs
     *
     * @return GetClassesRequest
     */
    public function setClassIDs(array $ClassIDs): GetClassesRequest
    {
        $this->ClassIDs = $ClassIDs;

        return $this;
    }
}