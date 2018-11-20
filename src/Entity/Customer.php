<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MiguelAlcaino\PaymentGateway\Interfaces\Entity\CustomerInterface;

/**
 * Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\CustomerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Customer implements CustomerInterface
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
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="merchantId", type="string", length=255)
     */
    private $merchantId;

    /**
     * @var string
     * @ORM\Column(name="payment_gateway_id", type="string", length=255, nullable=true)
     */
    private $paymentGatewayId;

    /**
     * @var CreditCard[]
     * @ORM\OneToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\CreditCard", mappedBy="customer")
     */
    private $creditCards;

    /**
     * @var TransactionRecord[]
     * @ORM\OneToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord", mappedBy="customer")
     * @ORM\OrderBy({"created" = "DESC"})
     */
    private $transactionRecords;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var string
     * @ORM\Column(name="user_country", type="string", nullable=true)
     */
    private $userCountry;

    /**
     * @var string
     * @ORM\Column(name="user_state", type="string", nullable=true)
     */
    private $userState;

    /**
     * @var string
     * @ORM\Column(name="user_city", type="string", nullable=true)
     */
    private $userCity;

    /**
     * @var string
     * @ORM\Column(name="document_type", type="string", nullable=true)
     */
    private $documentType;

    /**
     * @var string
     * @ORM\Column(name="document_number", type="string", nullable=true)
     */
    private $documentNumber;

    /**
     * @var CustomerDiscount[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount", mappedBy="customer")
     */
    private $customerDiscounts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->creditCards = new ArrayCollection();
        $this->transactionRecords = new ArrayCollection();
        $this->customerDiscounts = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf($this->firstName . ' ' . $this->lastName);
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
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Customer
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Customer
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * @return Customer
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * Set paymentGateway
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\PaymentGateway $paymentGateway
     *
     * @return Customer
     */
    public function setPaymentGateway(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\PaymentGateway $paymentGateway = null)
    {
        $this->paymentGateway = $paymentGateway;

        return $this;
    }

    /**
     * Get paymentGateway
     *
     * @return \MiguelAlcaino\MindbodyPaymentsBundle\Entity\PaymentGateway
     */
    public function getPaymentGateway()
    {
        return $this->paymentGateway;
    }

    /**
     * Get paymentGatewayId
     *
     * @return string
     */
    public function getPaymentGatewayId()
    {
        return $this->paymentGatewayId;
    }

    /**
     * Set paymentGatewayId
     *
     * @param string $paymentGatewayId
     *
     * @return Customer
     */
    public function setPaymentGatewayId($paymentGatewayId)
    {
        $this->paymentGatewayId = $paymentGatewayId;

        return $this;
    }

    /**
     * Add creditCard
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\CreditCard $creditCard
     *
     * @return Customer
     */
    public function addCreditCard(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\CreditCard $creditCard)
    {
        $this->creditCards[] = $creditCard;

        return $this;
    }

    /**
     * Remove creditCard
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\CreditCard $creditCard
     */
    public function removeCreditCard(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\CreditCard $creditCard)
    {
        $this->creditCards->removeElement($creditCard);
    }

    /**
     * Get creditCards
     *
     * @return CreditCard[]|Collection
     */
    public function getCreditCards()
    {
        return $this->creditCards;
    }

    /**
     * Add transactionRecord
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord
     *
     * @return Customer
     */
    public function addTransactionRecord(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord)
    {
        $this->transactionRecords[] = $transactionRecord;

        return $this;
    }

    /**
     * Remove transactionRecord
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord
     */
    public function removeTransactionRecord(\MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord $transactionRecord)
    {
        $this->transactionRecords->removeElement($transactionRecord);
    }

    /**
     * Get transactionRecords
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactionRecords()
    {
        return $this->transactionRecords;
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
     * @return Customer
     */
    public function setCreated()
    {
        $this->created = new \DateTime();

        return $this;
    }

    /**
     * Set userCountry
     *
     * @param string $userCountry
     *
     * @return Customer
     */
    public function setUserCountry($userCountry)
    {
        $this->userCountry = $userCountry;

        return $this;
    }

    /**
     * Get userCountry
     *
     * @return string
     */
    public function getUserCountry()
    {
        return $this->userCountry;
    }

    /**
     * Set userCity
     *
     * @param string $userCity
     *
     * @return Customer
     */
    public function setUserCity($userCity)
    {
        $this->userCity = $userCity;

        return $this;
    }

    /**
     * Get userCity
     *
     * @return string
     */
    public function getUserCity()
    {
        return $this->userCity;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     *
     * @return Customer
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set documentNumber
     *
     * @param string $documentNumber
     *
     * @return Customer
     */
    public function setDocumentNumber($documentNumber)
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    /**
     * Get documentNumber
     *
     * @return string
     */
    public function getDocumentNumber()
    {
        return $this->documentNumber;
    }

    /**
     * Set userState
     *
     * @param string $userState
     *
     * @return Customer
     */
    public function setUserState($userState)
    {
        $this->userState = $userState;

        return $this;
    }

    /**
     * Get userState
     *
     * @return string
     */
    public function getUserState()
    {
        return $this->userState;
    }

    /**
     * Add customerDiscount
     *
     * @param \MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount $customerDiscount
     *
     * @return Customer
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
     * @return \Doctrine\Common\Collections\Collection|CustomerDiscount[]
     */
    public function getCustomerDiscounts()
    {
        return $this->customerDiscounts;
    }
}
