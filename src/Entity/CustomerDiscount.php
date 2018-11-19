<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 12/03/18
 * Time: 14:31
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CustomerDiscount
 * @package MiguelAlcaino\MindbodyPaymentsBundle\Entity
 * @ORM\Table(name="customer_discount")
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\CustomerDiscountRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CustomerDiscount
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
     * @var Customer
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer", inversedBy="customerDiscounts")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @var Discount
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount", inversedBy="customerDiscounts")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $discount;

    /**
     * @var \DateTime
     * @ORM\Column(name="valid_from", type="datetime", nullable=false)
     */
    private $validFrom;

    /**
     * @var \DateTime
     * @ORM\Column(name="valid_until", type="datetime", nullable=false)
     */
    private $validUntil;

    /**
     * @var boolean
     * @ORM\Column(name="is_used", type="boolean", options={"default" = false})
     */
    private $isUsed;

    /**
     * @var \DateTime
     * @ORM\Column(name="when_used", type="datetime", nullable=true)
     */
    private $whenUsed;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     */
    private $created;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code;

    /**
     * @var TransactionRecord
     * @ORM\OneToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord", mappedBy="customerDiscount")
     */
    private $transactionRecord;

    /**
     * CustomerDiscount constructor.
     */
    public function __construct()
    {
        $this->isUsed = false;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get validFrom
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Set validFrom
     *
     * @param \DateTime $validFrom
     *
     * @return CustomerDiscount
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get validUntil
     *
     * @return \DateTime
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set validUntil
     *
     * @param \DateTime $validUntil
     *
     * @return CustomerDiscount
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get isUsed
     *
     * @return boolean
     */
    public function getIsUsed()
    {
        return $this->isUsed;
    }

    /**
     * Set isUsed
     *
     * @param boolean $isUsed
     *
     * @return CustomerDiscount
     */
    public function setIsUsed($isUsed)
    {
        $this->isUsed = $isUsed;

        return $this;
    }

    /**
     * Get whenUsed
     *
     * @return \DateTime
     */
    public function getWhenUsed()
    {
        return $this->whenUsed;
    }

    /**
     * Set whenUsed
     *
     * @param \DateTime $whenUsed
     *
     * @return CustomerDiscount
     */
    public function setWhenUsed($whenUsed)
    {
        $this->whenUsed = $whenUsed;

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
     * @param \DateTime $created
     *
     * @return CustomerDiscount
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return CustomerDiscount
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * @return CustomerDiscount
     */
    public function setCustomer(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get discount
     *
     * @return \MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set discount
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount $discount
     *
     * @return CustomerDiscount
     */
    public function setDiscount(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount $discount = null)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Set transactionRecord
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord
     *
     * @return CustomerDiscount
     */
    public function setTransactionRecord(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord = null)
    {
        $this->transactionRecord = $transactionRecord;

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
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->created = new \DateTime();
    }
}
