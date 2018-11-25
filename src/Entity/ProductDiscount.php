<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 11/03/18
 * Time: 21:09
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Class ProductDiscount
 * @package MiguelAlcaino\MindbodyPaymentsBundle\Entity
 * @ORM\Table(name="product_discount")
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\ProductDiscountRepository")
 */
class ProductDiscount
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
     * @var Product
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product", inversedBy="productDiscounts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var Discount
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount", inversedBy="productDiscounts")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $discount;

    /**
     * @var boolean
     * @ORM\Column(name="is_main_product", type="boolean")
     */
    private $isMainProduct = false;

    /**
     * @var boolean
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

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
     * Get isMainProduct
     *
     * @return boolean
     */
    public function getIsMainProduct()
    {
        return $this->isMainProduct;
    }

    /**
     * Set isMainProduct
     *
     * @param boolean $isMainProduct
     *
     * @return ProductDiscount
     */
    public function setIsMainProduct($isMainProduct)
    {
        $this->isMainProduct = $isMainProduct;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return ProductDiscount
     */
    public function setProduct(Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get discount
     *
     * @return Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set discount
     *
     * @param Discount $discount
     *
     * @return ProductDiscount
     */
    public function setDiscount(Discount $discount = null)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return ProductDiscount
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}
