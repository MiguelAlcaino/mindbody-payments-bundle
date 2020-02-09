<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\AbstractSOAPRequester;

class GetClassesRequest implements \JsonSerializable
{
    /**
     * @var array
     */
    private $ClassIDs;

    /**
     * @var \DateTimeImmutable
     */
    private $StartDateTime;

    /**
     * @var \DateTimeImmutable
     */
    private $EndDateTime;

    public function jsonSerialize()
    {
        $jsonArray = [];

        if ($this->ClassIDs !== null) {
            $jsonArray['ClassIDs'] = [
                'int' => $this->ClassIDs
            ];
        }

        if ($this->StartDateTime !== null) {
            $jsonArray['StartDateTime'] = $this->StartDateTime->format(AbstractSOAPRequester::DATE_MINDBODY_FORMAT);
        }

        if ($this->EndDateTime !== null) {
            $jsonArray['EndDateTime'] = $this->EndDateTime->format(AbstractSOAPRequester::DATE_MINDBODY_FORMAT);
        }

        return $jsonArray;
    }

    /**
     * @return array
     */
    public function getClassIDs(): ?array
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

    /**
     * @return \DateTimeImmutable
     */
    public function getStartDateTime(): ?\DateTimeImmutable
    {
        return $this->StartDateTime;
    }

    /**
     * @param \DateTimeImmutable $StartDateTime
     *
     * @return GetClassesRequest
     */
    public function setStartDateTime(\DateTimeImmutable $StartDateTime): GetClassesRequest
    {
        $this->StartDateTime = $StartDateTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndDateTime(): ?\DateTimeImmutable
    {
        return $this->EndDateTime;
    }

    /**
     * @param \DateTimeImmutable $EndDateTime
     *
     * @return GetClassesRequest
     */
    public function setEndDateTime(\DateTimeImmutable $EndDateTime): GetClassesRequest
    {
        $this->EndDateTime = $EndDateTime;

        return $this;
    }
}