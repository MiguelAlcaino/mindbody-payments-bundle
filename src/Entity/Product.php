<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"public"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"public"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255)
     * @Groups({"public"})
     */
    private $price;

    /**
     * @var string
     * @ORM\Column(name="merchant_id", type="string", length=255)
     */
    private $merchantId;

    /**
     * @var boolean
     * @ORM\Column(name="is_deleted", type="boolean", options={"default" = false})
     */
    private $isDeleted;

    /**
     * @var ProductDiscount[]
     * @ORM\OneToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount", mappedBy="product")
     */
    private $productDiscounts;

    public function __construct()
    {
        $this->isDeleted = false;
        $this->productDiscounts = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf($this->name);
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
     * @return Product
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
     * @return Product
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
     * @return Product
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return Product
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Add productDiscount
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount $productDiscount
     *
     * @return Product
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
     * @return ArrayCollection|Discount[]
     */
    public function getDiscounts()
    {
        $discounts = new ArrayCollection();

        foreach ($this->productDiscounts as $productDiscount) {
            $discounts->add($productDiscount->getDiscount());
        }

        return $discounts;
    }

    public function getMainDiscount()
    {

        foreach ($this->productDiscounts as $productDiscount) {
            if ($productDiscount->getIsMainProduct()) {
                return $productDiscount->getDiscount();
            }
        }

        return null;
    }
}
