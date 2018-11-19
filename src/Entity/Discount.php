<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Discount
 *
 * @ORM\Table(name="discount")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Discount
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
     * @ORM\Column(name="beforeOrAfter", type="string", length=255, nullable=true)
     */
    private $beforeOrAfter;

    /**
     * @var int
     *
     * @ORM\Column(name="days", type="integer", nullable=true)
     */
    private $days;

    /**
     * @var float
     *
     * @ORM\Column(name="discountPercentage", type="float", nullable=false)
     */
    private $discountPercentage;


    /**
     * @var string
     * @ORM\Column(name="email_body", type="text", nullable=false)
     */
    private $emailBody;

    /**
     * @var string
     * @ORM\Column(name="email_subject", type="text", nullable=false)
     */
    private $emailSubject;

    /**
     * @var ProductDiscount[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount", mappedBy="discount")
     */
    private $productDiscounts;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     */
    private $created;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated", type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     */
    private $updated;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", options={"default" = true})
     */
    private $enabled;

    /**
     * @var CustomerDiscount[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount", mappedBy="discount")
     */
    private $customerDiscounts;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255)
     * Possible values:
     *  product_discount
     *  client_discount
     */
    private $type;

    /**
     * @var integer
     * @ORM\Column(name="send_again_every_number", type="integer", options={"default" = 12})
     */
    private $sendAgainEveryNumber;

    /**
     * Possible values
     *  - month
     *  - purchase
     * @var string
     * @ORM\Column(name="send_again_date_type", type="string", length=10, options={"default" = "purchase"})
     */
    private $sendAgainDateType;

    /**
     * @var int
     * @ORM\Column(name="additional_days", type="integer", options={"default" = 1})
     */
    private $additionalDays;

    /**
     * Discount constructor.
     */
    public function __construct()
    {
        $this->productDiscounts = new ArrayCollection();
        $this->enabled = true;
        $this->customerDiscounts = new ArrayCollection();
        $this->sendAgainEveryNumber = 12;
        $this->sendAgainDateType = 'purchase';
        $this->additionalDays = 1;
    }


    public function __toString()
    {
        return $this->discountPercentage . '% de descuendo' . ($this->beforeOrAfter === 'before' ? 'antes' : 'despues') . ' de ' . $this->days . ' dias';
    }

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
     * Get beforeOrAfter
     *
     * @return string
     */
    public function getBeforeOrAfter()
    {
        return $this->beforeOrAfter;
    }

    /**
     * Set beforeOrAfter
     *
     * @param string $beforeOrAfter
     *
     * @return Discount
     */
    public function setBeforeOrAfter($beforeOrAfter)
    {
        $this->beforeOrAfter = $beforeOrAfter;

        return $this;
    }

    /**
     * Get days
     *
     * @return int
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set days
     *
     * @param integer $days
     *
     * @return Discount
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get discountPercentage
     *
     * @return float
     */
    public function getDiscountPercentage()
    {
        return $this->discountPercentage;
    }

    /**
     * Set discountPercentage
     *
     * @param float $discountPercentage
     *
     * @return Discount
     */
    public function setDiscountPercentage($discountPercentage)
    {
        $this->discountPercentage = $discountPercentage;

        return $this;
    }

    /**
     * Get emailBody
     *
     * @return string
     */
    public function getEmailBody()
    {
        return $this->emailBody;
    }

    /**
     * Set emailBody
     *
     * @param string $emailBody
     *
     * @return Discount
     */
    public function setEmailBody($emailBody)
    {
        $this->emailBody = $emailBody;

        return $this;
    }

    /**
     * Get emailSubject
     *
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }

    /**
     * Set emailSubject
     *
     * @param string $emailSubject
     *
     * @return Discount
     */
    public function setEmailSubject($emailSubject)
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    /**
     * Add productDiscount
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount $productDiscount
     *
     * @return Discount
     */
    public function addProductDiscount(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount $productDiscount)
    {
        $this->productDiscounts[] = $productDiscount;

        return $this;
    }

    /**
     * Remove productDiscount
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount $productDiscount
     */
    public function removeProductDiscount(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount $productDiscount)
    {
        $this->productDiscounts->removeElement($productDiscount);
    }

    /**
     * Get productDiscounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDiscounts()
    {
        return $this->productDiscounts;
    }

    /**
     * @param $productDiscounts
     */
    public function setProductDiscounts($productDiscounts)
    {
        $this->productDiscounts = $productDiscounts;
    }

    /**
     * @return ArrayCollection|Product[]
     */
    public function getProducts()
    {
        $products = new ArrayCollection();

        foreach ($this->productDiscounts as $productDiscount) {
            $products->add($productDiscount->getProduct());
        }

        return $products;
    }

    /**
     * @return ArrayCollection|Product[]
     */
    public function getActiveProducts()
    {
        $products = new ArrayCollection();

        foreach ($this->productDiscounts as $productDiscount) {
            if ($productDiscount->getActive()) {
                $products->add($productDiscount->getProduct());
            }
        }

        return $products;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreated()
    {
        $now = new \DateTime();
        $this->created = $now;
        $this->updated = $now;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setUpdated()
    {
        $this->updated = new \DateTime();
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Discount
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Add customerDiscount
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount $customerDiscount
     *
     * @return Discount
     */
    public function addCustomerDiscount(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount $customerDiscount)
    {
        $this->customerDiscounts[] = $customerDiscount;

        return $this;
    }

    /**
     * Remove customerDiscount
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount $customerDiscount
     */
    public function removeCustomerDiscount(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount $customerDiscount)
    {
        $this->customerDiscounts->removeElement($customerDiscount);
    }

    /**
     * Get customerDiscounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomerDiscounts()
    {
        return $this->customerDiscounts;
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
     * @return Discount
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getMainProduct()
    {
        foreach ($this->productDiscounts as $productDiscount) {
            if ($productDiscount->getIsMainProduct()) {
                return $productDiscount->getProduct();
            }
        }
        return null;
    }

    /**
     * Set sendAgainEveryNumber
     *
     * @param integer $sendAgainEveryNumber
     *
     * @return Discount
     */
    public function setSendAgainEveryNumber($sendAgainEveryNumber)
    {
        $this->sendAgainEveryNumber = $sendAgainEveryNumber;

        return $this;
    }

    /**
     * Get sendAgainEveryNumber
     *
     * @return integer
     */
    public function getSendAgainEveryNumber()
    {
        return $this->sendAgainEveryNumber;
    }

    /**
     * Set sendAgainDateType
     *
     * @param string $sendAgainDateType
     *
     * @return Discount
     */
    public function setSendAgainDateType($sendAgainDateType)
    {
        $this->sendAgainDateType = $sendAgainDateType;

        return $this;
    }

    /**
     * Get sendAgainDateType
     *
     * @return string
     */
    public function getSendAgainDateType()
    {
        return $this->sendAgainDateType;
    }

    /**
     * Set additionalDays
     *
     * @param integer $additionalDays
     *
     * @return Discount
     */
    public function setAdditionalDays($additionalDays)
    {
        $this->additionalDays = $additionalDays;

        return $this;
    }

    /**
     * Get additionalDays
     *
     * @return integer
     */
    public function getAdditionalDays()
    {
        return $this->additionalDays;
    }
}
