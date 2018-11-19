<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CreditCard
 *
 * @ORM\Table(name="credit_card")
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\CreditCardRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CreditCard
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="cardHolderName", type="string", length=255)
     */
    private $cardHolderName;

    /**
     * @var string
     * @ORM\Column(name="last_four_digits", type="string", length=4, nullable=false)
     */
    private $lastFourDigits;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=10, nullable=false)
     */
    private $type;

    /**
     * @var string
     */
    private $urlTemporalToken;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var Customer
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer", inversedBy="creditCards")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return CreditCard
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get cardHolderName
     *
     * @return string
     */
    public function getCardHolderName()
    {
        return $this->cardHolderName;
    }

    /**
     * Set cardHolderName
     *
     * @param string $cardHolderName
     *
     * @return CreditCard
     */
    public function setCardHolderName($cardHolderName)
    {
        $this->cardHolderName = $cardHolderName;

        return $this;
    }

    /**
     * Get lastFourDigits
     *
     * @return string
     */
    public function getLastFourDigits()
    {
        return $this->lastFourDigits;
    }

    /**
     * Set lastFourDigits
     *
     * @param string $lastFourDigits
     *
     * @return CreditCard
     */
    public function setLastFourDigits($lastFourDigits)
    {
        $this->lastFourDigits = $lastFourDigits;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set customer
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer $customer
     *
     * @return CreditCard
     */
    public function setCustomer(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return CreditCard
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlTemporalToken()
    {
        return $this->urlTemporalToken;
    }

    /**
     * @param string $urlTemporalToken
     * @return CreditCard
     */
    public function setUrlTemporalToken(string $urlTemporalToken)
    {
        $this->urlTemporalToken = $urlTemporalToken;
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created
     *
     * @ORM\PrePersist()
     *
     * @return CreditCard
     */
    public function setCreated()
    {
        $this->created = new \DateTime();

        return $this;
    }
}
