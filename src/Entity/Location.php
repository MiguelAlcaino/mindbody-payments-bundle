<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\LocationRepository")
 */
class Location
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $merchantId;

    /**
     * @var Product[]
     * @ORM\ManyToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product", inversedBy="locations")
     */
    private $products;

    /**
     * @ORM\Column(type="boolean", options={"default" = false})
     */
    private $isDeleted;

    /**
     * Location constructor.
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->isDeleted = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): self
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function addProduct(Product $product){
        $this->products->add($product);

        return $this;
    }

    /**
     * @param Product $product
     */
    public function removeProduct(Product $product){
        $this->products->removeElement($product);
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
