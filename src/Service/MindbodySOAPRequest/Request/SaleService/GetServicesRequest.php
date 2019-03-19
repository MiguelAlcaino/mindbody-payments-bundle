<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\SaleService;

class GetServicesRequest implements \JsonSerializable
{
    /**
     * @var array
     */
    private $ProgramIDs;

    /**
     * @var int
     */
    private $ClassScheduleID;

    /**
     * @var int
     */
    private $LocationID;

    /**
     * @var bool
     */
    private $SellOnline;

    public function jsonSerialize()
    {
        $jsonArray = [];
        if ($this->ProgramIDs !== null) {
            $jsonArray['ProgramIDs'] = [
                'int' => $this->ProgramIDs,
            ];
        }

        if ($this->ClassScheduleID !== null) {
            $jsonArray['ClassScheduleID'] = $this->ClassScheduleID;
        }

        if ($this->LocationID !== null) {
            $jsonArray['LocationID'] = $this->LocationID;
        }

        if ($this->SellOnline !== null) {
            $jsonArray['SellOnline'] = $this->SellOnline;
        }

        return $jsonArray;
    }

    /**
     * @return array
     */
    public function getProgramIDs(): ?array
    {
        return $this->ProgramIDs;
    }

    /**
     * @param array $ProgramIDs
     *
     * @return GetServicesRequest
     */
    public function setProgramIDs(array $ProgramIDs): GetServicesRequest
    {
        $this->ProgramIDs = $ProgramIDs;

        return $this;
    }

    /**
     * @return int
     */
    public function getClassScheduleID(): ?int
    {
        return $this->ClassScheduleID;
    }

    /**
     * @param int $ClassScheduleID
     *
     * @return GetServicesRequest
     */
    public function setClassScheduleID(int $ClassScheduleID): GetServicesRequest
    {
        $this->ClassScheduleID = $ClassScheduleID;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocationID()
    {
        return $this->LocationID;
    }

    /**
     * @param mixed $LocationID
     *
     * @return GetServicesRequest
     */
    public function setLocationID($LocationID): self
    {
        $this->LocationID = $LocationID;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSellOnline(): bool
    {
        return $this->SellOnline;
    }

    /**
     * @param bool $SellOnline
     *
     * @return GetServicesRequest
     */
    public function setSellOnline(bool $SellOnline): GetServicesRequest
    {
        $this->SellOnline = $SellOnline;

        return $this;
    }


}