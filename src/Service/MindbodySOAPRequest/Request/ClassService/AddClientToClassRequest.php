<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\ClassService;

class AddClientToClassRequest implements \JsonSerializable
{
    /**
     * @var string
     */
    private $ClientID;

    /**
     * @var int
     */
    private $ClassID;

    public function jsonSerialize()
    {
        return [
            'ClientID' => $this->ClientID,
            'ClassID'  => $this->ClassID,
        ];
    }

    /**
     * @return string
     */
    public function getClientID(): string
    {
        return $this->ClientID;
    }

    /**
     * @param string $ClientID
     *
     * @return AddClientToClassRequest
     */
    public function setClientID(string $ClientID): AddClientToClassRequest
    {
        $this->ClientID = $ClientID;

        return $this;
    }

    /**
     * @return int
     */
    public function getClassID(): int
    {
        return $this->ClassID;
    }

    /**
     * @param int $ClassID
     *
     * @return AddClientToClassRequest
     */
    public function setClassID(int $ClassID): AddClientToClassRequest
    {
        $this->ClassID = $ClassID;

        return $this;
    }
}