<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService;

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

    /**
     * AddClientToClassRequest constructor.
     *
     * @param string $ClientID
     * @param int    $ClassID
     */
    public function __construct(string $ClientID, int $ClassID)
    {
        $this->ClientID = $ClientID;
        $this->ClassID  = $ClassID;
    }

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
     * @return int
     */
    public function getClassID(): int
    {
        return $this->ClassID;
    }
}