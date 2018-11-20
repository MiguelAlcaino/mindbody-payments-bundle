<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MiguelAlcaino\PaymentGateway\Interfaces\Entity\TransactionItemInterface;

/**
 * TransactionItem
 *
 * @ORM\Table(name="transaction_item")
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\TransactionItemRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TransactionItem implements TransactionItemInterface
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="merchantId", type="string", length=255)
     */
    private $merchantId;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var TransactionRecord
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord", inversedBy="transactionItems")
     * @ORM\JoinColumn(name="transaction_record_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $transactionRecord;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255, options={"default" = "service"})
     */
    private $type = 'service';

    /**
     * @var int
     * @ORM\Column(name="program_id", type="integer", nullable=true)
     */
    private $programId;

    /**
     * @var \DateTime
     * @ORM\Column(name="active_date", type="datetime", nullable=true)
     */
    private $activeDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    private $expirationDate;

    /**
     * @var string
     * @ORM\Column(name="mindbody_service_id", type="string", length=255, nullable=true)
     */
    private $mindbodyServiceId;

    /**
     * @var \DateTime
     * @ORM\Column(name="sale_datetime", type="datetime", nullable=false)
     */
    private $saleDatetime;

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TransactionItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return TransactionItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get merchantId
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * Set merchantId
     *
     * @param string $merchantId
     *
     * @return TransactionItem
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return TransactionItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get transactionRecord
     *
     * @return \MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord
     */
    public function getTransactionRecord()
    {
        return $this->transactionRecord;
    }

    /**
     * Set transactionRecord
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord
     *
     * @return TransactionItem
     */
    public function setTransactionRecord(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord = null)
    {
        $this->transactionRecord = $transactionRecord;

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
     * @return TransactionItem
     */
    public function setCreated()
    {
        $this->created = new \DateTime();

        return $this;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return TransactionItem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set programId.
     *
     * @param int|null $programId
     *
     * @return TransactionItem
     */
    public function setProgramId($programId = null)
    {
        $this->programId = $programId;

        return $this;
    }

    /**
     * Get programId.
     *
     * @return int|null
     */
    public function getProgramId()
    {
        return $this->programId;
    }

    /**
     * Set activeDate.
     *
     * @param \DateTime|null $activeDate
     *
     * @return TransactionItem
     */
    public function setActiveDate($activeDate = null)
    {
        $this->activeDate = $activeDate;

        return $this;
    }

    /**
     * Get activeDate.
     *
     * @return \DateTime|null
     */
    public function getActiveDate()
    {
        return $this->activeDate;
    }

    /**
     * Set expirationDate.
     *
     * @param \DateTime $expirationDate
     *
     * @return TransactionItem
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate.
     *
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set mindbodyServiceId.
     *
     * @param string|null $mindbodyServiceId
     *
     * @return TransactionItem
     */
    public function setMindbodyServiceId($mindbodyServiceId = null)
    {
        $this->mindbodyServiceId = $mindbodyServiceId;

        return $this;
    }

    /**
     * Get mindbodyServiceId.
     *
     * @return string|null
     */
    public function getMindbodyServiceId()
    {
        return $this->mindbodyServiceId;
    }

    /**
     * Set saleDatetime.
     *
     * @param \DateTime $saleDatetime
     *
     * @return TransactionItem
     */
    public function setSaleDatetime($saleDatetime)
    {
        $this->saleDatetime = $saleDatetime;

        return $this;
    }

    /**
     * Get saleDatetime.
     *
     * @return \DateTime
     */
    public function getSaleDatetime()
    {
        return $this->saleDatetime;
    }
}
