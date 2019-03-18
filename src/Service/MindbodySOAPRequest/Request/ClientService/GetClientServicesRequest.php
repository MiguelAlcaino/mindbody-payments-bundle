<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\ClientService;

class GetClientServicesRequest implements \JsonSerializable
{
    /**
     * @var string
     */
    private $ClientID;

    /**
     * @var array
     */
    private $ProgramIDs;

    /**
     * @var int
     */
    private $ClassID;

    /**
     * @var boolean
     */
    private $ShowActiveOnly;

    /**
     * GetClientServicesRequest constructor.
     *
     * @param string $ClientID
     * @param array  $ProgramIDs
     */
    public function __construct(string $ClientID, array $ProgramIDs)
    {
        $this->ClientID   = $ClientID;
        $this->ProgramIDs = $ProgramIDs;
    }

    public function jsonSerialize()
    {
        $jsonArray = [
            'ClientID' => $this->ClientID,
            'ProgramIDs' => [
                'int' => $this->ProgramIDs
            ]
        ];

        if($this->ClassID !== null){
            $jsonArray['ClassID'] = $this->ClassID;
        }

        if($this->ShowActiveOnly !== null){
            $jsonArray['ShowActiveOnly'] = $this->ShowActiveOnly;
        }

        return $jsonArray;
    }

    /**
     * @return int
     */
    public function getClassID(): ?int
    {
        return $this->ClassID;
    }

    /**
     * @param int $ClassID
     *
     * @return GetClientServicesRequest
     */
    public function setClassID(int $ClassID): GetClientServicesRequest
    {
        $this->ClassID = $ClassID;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientID(): string
    {
        return $this->ClientID;
    }

    /**
     * @return array
     */
    public function getProgramIDs(): array
    {
        return $this->ProgramIDs;
    }
}