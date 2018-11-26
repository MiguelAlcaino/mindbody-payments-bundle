<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use MiguelAlcaino\PaymentGateway\Interfaces\Entity\TransactionItemInterface;
use MiguelAlcaino\PaymentGateway\Interfaces\Entity\TransactionRecordInterface;

/**
 * TransactionRecord
 *
 * @ORM\Table(name="transaction_record")
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\TransactionRecordRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TransactionRecord implements TransactionRecordInterface
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
     * @ORM\Column(name="creditCardChargeId", type="string", length=255, nullable=true)
     */
    private $creditCardChargeId;

    /**
     * @var string
     * @ORM\Column(name="credit_card_holder_name", type="string", length=255, nullable=true)
     */
    private $creditCardHolderName;

    /**
     * @var string
     * @ORM\Column(name="credit_card_last_four_digits", type="string", length=255, nullable=true)
     */
    private $creditCardLastFourDigits;

    /**
     * @var string
     * @ORM\Column(name="credit_card_number", type="string", length=19, nullable=true)
     */
    private $creditCardNumber;

    /**
     * @var string
     * @ORM\Column(name="credit_card_expiration_month", type="string", length=2, nullable=true)
     */
    private $creditCardExpirationMonth;

    /**
     * @var string
     * @ORM\Column(name="credit_card_expiration_year", type="string", length=2, nullable=true)
     */
    private $creditCardExpirationYear;

    /**
     * @var CreditCard
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\CreditCard", inversedBy="transactionRecords")
     * @ORM\JoinColumn(name="credit_card_id", referencedColumnName="id", nullable=true)
     */
    private $creditCard;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="string", length=255, nullable=true)
     */
    private $amount;

    /**
     * Amount calculated before the purchase is made
     * @var string
     * @ORM\Column(name="pre_amount", type="string", length=255, nullable=true)
     */
    private $preAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="taxAmount", type="string", length=255)
     */
    private $taxAmount = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentTransaction", type="string", length=255, nullable=true)
     */
    private $paymentTransaction;

    /**
     * @var string
     *
     * @ORM\Column(name="merchantId", type="string", length=255, nullable=true)
     */
    private $merchantId;

    /**
     * @var TransactionItem[]
     * @ORM\OneToMany(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionItem", mappedBy="transactionRecord", cascade={"persist"})
     */
    private $transactionItems;

    /**
     * @var Customer
     * @ORM\ManyToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer", inversedBy="transactionRecords")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     */
    private $customer;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     * @ORM\Column(name="merchant_response", type="text", nullable=true)
     */
    private $merchantResponse;

    /**
     * @var string
     * @ORM\Column(name="payment_gateway_response", type="text", nullable=true)
     */
    private $paymentGatewayResponse;

    /**
     * @var string
     * @ORM\Column(name="payment_gateway_fee", type="string", nullable=true)
     */
    private $paymentGatewayFee;

    /**
     * @var \DateTime
     * @ORM\Column(name="refund_date", type="datetime", nullable=true)
     */
    private $refundDate;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(name="refund_response", type="text", nullable=true)
     */
    private $refundResponse;

    /**
     * @var string
     * @ORM\Column(name="refund_id", type="string", length=255, nullable=true)
     */
    private $refundId;

    /**
     * @var int
     * @ORM\Column(name="merchant_purchase_id", type="integer", nullable=true)
     */
    private $merchantPurchaseId;

    /**
     * @var int
     * @ORM\Column(name="installments", type="integer", nullable=false, options={"default" = 1})
     */
    private $installments;

    /**
     * @var string
     * @ORM\Column(name="authorization_code", type="string", length=255, nullable=true)
     */
    private $authorizationCode;

    /**
     * @var string
     * @ORM\Column(name="user_preferred_location", type="string", length=255, nullable=true)
     */
    private $userPreferredLocation;

    /**
     * @var string
     * @ORM\Column(name="discount_code", type="string", length=255, nullable=true)
     */
    private $discountCode;

    /**
     * @var string
     * @ORM\Column(name="discount_amount", type="string", length=255, nullable=true)
     */
    private $discountAmount;

    /**
     * @var \DateTime
     * @ORM\Column(name="service_expiration_date", type="datetime", nullable=true)
     */
    private $serviceExpirationDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="service_activation_date", type="datetime", nullable=true)
     */
    private $serviceActivationDate;

    /**
     * @var string
     * @ORM\Column(name="user_country", type="string", nullable=false)
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
     * @var boolean
     * @ORM\Column(name="user_location_updated", type="boolean")
     */
    private $userLocationUpdated;

    /**
     * @var boolean
     * @ORM\Column(name="user_location_updated_error", type="boolean")
     */
    private $userLocationUpdatedError;

    /**
     * @var bool
     * @ORM\Column(name="mindbody_checkout_fail", type="boolean", options={"default" = false})
     */
    private $mindbodyCheckoutFail;

    /**
     * @var bool
     * @ORM\Column(name="mindbody_last_purchase_fail", type="boolean", options={"default" = false})
     */
    private $mindbodyLastPurchaseFail;

    /**
     * @var bool
     * @ORM\Column(name="mindbody_expiration_date_fail", type="boolean", options={"default" = false})
     */
    private $mindbodyExpirationDateFail;

    /**
     * @var int
     * @ORM\Column(name="mindbody_payment_method_id", type="integer", nullable=true)
     */
    private $mindbodyPaymentMethodId;

    /**
     * @var int
     * @ORM\Column(name="service_id", type="integer", nullable=true)
     */
    private $serviceId;

    /**
     * @var CustomerDiscount
     * @ORM\OneToOne(targetEntity="MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount", inversedBy="transactionRecord")
     * @ORM\JoinColumn(name="customer_discount_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $customerDiscount;

    /**
     * TransactionRecord constructor.
     */
    public function __construct()
    {
        $this->transactionItems = new ArrayCollection();
        $this->installments = 1;
        $this->userLocationUpdated = false;
        $this->userLocationUpdatedError = false;
        $this->mindbodyCheckoutFail = false;
        $this->mindbodyLastPurchaseFail = false;
        $this->mindbodyExpirationDateFail = false;
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
     * Get creditCardChargeId
     *
     * @return string
     */
    public function getCreditCardChargeId()
    {
        return $this->creditCardChargeId;
    }

    /**
     * Set creditCardChargeId
     *
     * @param string $creditCardChargeId
     *
     * @return TransactionRecord
     */
    public function setCreditCardChargeId($creditCardChargeId)
    {
        $this->creditCardChargeId = $creditCardChargeId;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return TransactionRecord
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get taxAmount
     *
     * @return string
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * Set taxAmount
     *
     * @param string $taxAmount
     *
     * @return TransactionRecord
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    /**
     * Get paymentTransaction
     *
     * @return string
     */
    public function getPaymentTransaction()
    {
        return $this->paymentTransaction;
    }

    /**
     * Set paymentTransaction
     *
     * @param string $paymentTransaction
     *
     * @return TransactionRecord
     */
    public function setPaymentTransaction($paymentTransaction)
    {
        $this->paymentTransaction = $paymentTransaction;

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
     * @return TransactionRecord
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * Add transactionItem
     *
     * @param TransactionItemInterface $transactionItem
     *
     * @return TransactionRecord
     */
    public function addTransactionItem(TransactionItemInterface $transactionItem)
    {
        $transactionItem->setTransactionRecord($this);
        $this->transactionItems[] = $transactionItem;

        return $this;
    }

    /**
     * Remove transactionItem
     *
     * @param TransactionItemInterface $transactionItem
     */
    public function removeTransactionItem(TransactionItemInterface $transactionItem)
    {
        $this->transactionItems->removeElement($transactionItem);
    }

    /**
     * Get transactionItems
     *
     * @return \Doctrine\Common\Collections\Collection|TransactionItem[]
     */
    public function getTransactionItems()
    {
        return $this->transactionItems;
    }

    /**
     * @return $this
     */
    public function wipeTransactionItems()
    {
        $this->transactionItems = new ArrayCollection();
        return $this;
    }

    /**
     * Get customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set customer
     *
     * @param Customer $customer
     *
     * @return TransactionRecord
     */
    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;

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
     *
     * @ORM\PrePersist()
     * @return TransactionRecord
     *
     */
    public function setCreated()
    {
        $this->created = new \DateTime();

        return $this;
    }

    /**
     * Get merchantResponse
     *
     * @return string
     */
    public function getMerchantResponse()
    {
        return $this->merchantResponse;
    }

    /**
     * Set merchantResponse
     *
     * @param string $merchantResponse
     *
     * @return TransactionRecord
     */
    public function setMerchantResponse($merchantResponse)
    {
        $this->merchantResponse = $merchantResponse;

        return $this;
    }

    /**
     * Get paymentGatewayResponse
     *
     * @return string
     */
    public function getPaymentGatewayResponse()
    {
        return $this->paymentGatewayResponse;
    }

    /**
     * Set paymentGatewayResponse
     *
     * @param string $paymentGatewayResponse
     *
     * @return TransactionRecord
     */
    public function setPaymentGatewayResponse($paymentGatewayResponse)
    {
        $this->paymentGatewayResponse = $paymentGatewayResponse;

        return $this;
    }

    /**
     * Get paymentGatewayFee
     *
     * @return string
     */
    public function getPaymentGatewayFee()
    {
        return $this->paymentGatewayFee;
    }

    /**
     * Set paymentGatewayFee
     *
     * @param string $paymentGatewayFee
     *
     * @return TransactionRecord
     */
    public function setPaymentGatewayFee($paymentGatewayFee)
    {
        $this->paymentGatewayFee = $paymentGatewayFee;

        return $this;
    }

    /**
     * Get refundDate
     *
     * @return \DateTime
     */
    public function getRefundDate()
    {
        return $this->refundDate;
    }

    /**
     * Set refundDate
     *
     * @param \DateTime $refundDate
     *
     * @return TransactionRecord
     */
    public function setRefundDate($refundDate)
    {
        $this->refundDate = $refundDate;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return TransactionRecord
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get refundResponse
     *
     * @return string
     */
    public function getRefundResponse()
    {
        return $this->refundResponse;
    }

    /**
     * Set refundResponse
     *
     * @param string $refundResponse
     *
     * @return TransactionRecord
     */
    public function setRefundResponse($refundResponse)
    {
        $this->refundResponse = $refundResponse;

        return $this;
    }

    /**
     * Get merchantPurchaseId
     *
     * @return integer
     */
    public function getMerchantPurchaseId()
    {
        return $this->merchantPurchaseId;
    }

    /**
     * Set merchantPurchaseId
     *
     * @param integer $merchantPurchaseId
     *
     * @return TransactionRecord
     */
    public function setMerchantPurchaseId($merchantPurchaseId)
    {
        $this->merchantPurchaseId = $merchantPurchaseId;

        return $this;
    }

    /**
     * Get installments
     *
     * @return integer
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * Set installments
     *
     * @param integer $installments
     *
     * @return TransactionRecord
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;

        return $this;
    }

    /**
     * Get authorizationCode
     *
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Set authorizationCode
     *
     * @param string $authorizationCode
     *
     * @return TransactionRecord
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * Set userPreferredLocation
     *
     * @param string $userPreferredLocation
     *
     * @return TransactionRecord
     */
    public function setUserPreferredLocation($userPreferredLocation)
    {
        $this->userPreferredLocation = $userPreferredLocation;

        return $this;
    }

    /**
     * Get userPreferredLocation
     *
     * @return string
     */
    public function getUserPreferredLocation()
    {
        return $this->userPreferredLocation;
    }

    /**
     * Set discountCode
     *
     * @param string $discountCode
     *
     * @return TransactionRecord
     */
    public function setDiscountCode($discountCode)
    {
        $this->discountCode = $discountCode;

        return $this;
    }

    /**
     * Get discountCode
     *
     * @return string
     */
    public function getDiscountCode()
    {
        return $this->discountCode;
    }

    /**
     * Set discountAmount
     *
     * @param string $discountAmount
     *
     * @return TransactionRecord
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * Get discountAmount
     *
     * @return string
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * Set serviceExpirationDate
     *
     * @param \DateTime $serviceExpirationDate
     *
     * @return TransactionRecord
     */
    public function setServiceExpirationDate($serviceExpirationDate)
    {
        $this->serviceExpirationDate = $serviceExpirationDate;

        return $this;
    }

    /**
     * Get serviceExpirationDate
     *
     * @return \DateTime
     */
    public function getServiceExpirationDate()
    {
        return $this->serviceExpirationDate;
    }

    /**
     * Set serviceActivationDate
     *
     * @param \DateTime $serviceActivationDate
     *
     * @return TransactionRecord
     */
    public function setServiceActivationDate($serviceActivationDate)
    {
        $this->serviceActivationDate = $serviceActivationDate;

        return $this;
    }

    /**
     * Get serviceActivationDate
     *
     * @return \DateTime
     */
    public function getServiceActivationDate()
    {
        return $this->serviceActivationDate;
    }

    /**
     * Set userCountry
     *
     * @param string $userCountry
     *
     * @return TransactionRecord
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
     * @return TransactionRecord
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
     * @return TransactionRecord
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
     * @return TransactionRecord
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
     * @return TransactionRecord
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
     * Set creditCardHolderName
     *
     * @param string $creditCardHolderName
     *
     * @return TransactionRecord
     */
    public function setCreditCardHolderName($creditCardHolderName)
    {
        $this->creditCardHolderName = $creditCardHolderName;

        return $this;
    }

    /**
     * Get creditCardHolderName
     *
     * @return string
     */
    public function getCreditCardHolderName()
    {
        return $this->creditCardHolderName;
    }

    /**
     * Set creditCardLastFourDigits
     *
     * @param string $creditCardLastFourDigits
     *
     * @return TransactionRecord
     */
    public function setCreditCardLastFourDigits($creditCardLastFourDigits)
    {
        $this->creditCardLastFourDigits = $creditCardLastFourDigits;

        return $this;
    }

    /**
     * Get creditCardLastFourDigits
     *
     * @return string
     */
    public function getCreditCardLastFourDigits()
    {
        return $this->creditCardLastFourDigits;
    }

    /**
     * Set userLocationUpdated
     *
     * @param boolean $userLocationUpdated
     *
     * @return TransactionRecord
     */
    public function setUserLocationUpdated($userLocationUpdated)
    {
        $this->userLocationUpdated = $userLocationUpdated;

        return $this;
    }

    /**
     * Get userLocationUpdated
     *
     * @return boolean
     */
    public function getUserLocationUpdated()
    {
        return $this->userLocationUpdated;
    }

    /**
     * Set userLocationUpdatedError
     *
     * @param boolean $userLocationUpdatedError
     *
     * @return TransactionRecord
     */
    public function setUserLocationUpdatedError($userLocationUpdatedError)
    {
        $this->userLocationUpdatedError = $userLocationUpdatedError;

        return $this;
    }

    /**
     * Get userLocationUpdatedError
     *
     * @return boolean
     */
    public function getUserLocationUpdatedError()
    {
        return $this->userLocationUpdatedError;
    }

    /**
     * Set mindbodyCheckoutFail
     *
     * @param boolean $mindbodyCheckoutFail
     *
     * @return TransactionRecord
     */
    public function setMindbodyCheckoutFail($mindbodyCheckoutFail)
    {
        $this->mindbodyCheckoutFail = $mindbodyCheckoutFail;

        return $this;
    }

    /**
     * Get mindbodyCheckoutFail
     *
     * @return boolean
     */
    public function getMindbodyCheckoutFail()
    {
        return $this->mindbodyCheckoutFail;
    }

    /**
     * Set mindbodyLastPurchaseFail
     *
     * @param boolean $mindbodyLastPurchaseFail
     *
     * @return TransactionRecord
     */
    public function setMindbodyLastPurchaseFail($mindbodyLastPurchaseFail)
    {
        $this->mindbodyLastPurchaseFail = $mindbodyLastPurchaseFail;

        return $this;
    }

    /**
     * Get mindbodyLastPurchaseFail
     *
     * @return boolean
     */
    public function getMindbodyLastPurchaseFail()
    {
        return $this->mindbodyLastPurchaseFail;
    }

    /**
     * Set mindbodyExpirationDateFail
     *
     * @param boolean $mindbodyExpirationDateFail
     *
     * @return TransactionRecord
     */
    public function setMindbodyExpirationDateFail($mindbodyExpirationDateFail)
    {
        $this->mindbodyExpirationDateFail = $mindbodyExpirationDateFail;

        return $this;
    }

    /**
     * Get mindbodyExpirationDateFail
     *
     * @return boolean
     */
    public function getMindbodyExpirationDateFail()
    {
        return $this->mindbodyExpirationDateFail;
    }

    /**
     * Set mindbodyPaymentMethodId
     *
     * @param integer $mindbodyPaymentMethodId
     *
     * @return TransactionRecord
     */
    public function setMindbodyPaymentMethodId($mindbodyPaymentMethodId)
    {
        $this->mindbodyPaymentMethodId = $mindbodyPaymentMethodId;

        return $this;
    }

    /**
     * Get mindbodyPaymentMethodId
     *
     * @return integer
     */
    public function getMindbodyPaymentMethodId()
    {
        return $this->mindbodyPaymentMethodId;
    }

    /**
     * Set preAmount
     *
     * @param string $preAmount
     *
     * @return TransactionRecord
     */
    public function setPreAmount($preAmount)
    {
        $this->preAmount = $preAmount;

        return $this;
    }

    /**
     * Get preAmount
     *
     * @return string
     */
    public function getPreAmount()
    {
        return $this->preAmount;
    }

    /**
     * Set serviceId
     *
     * @param integer $serviceId
     *
     * @return TransactionRecord
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set customerDiscount
     *
     * @param \App\Entity\CustomerDiscount $customerDiscount
     *
     * @return TransactionRecord
     */
    public function setCustomerDiscount(\App\Entity\CustomerDiscount $customerDiscount = null)
    {
        $this->customerDiscount = $customerDiscount;

        return $this;
    }

    /**
     * Get customerDiscount
     *
     * @return \App\Entity\CustomerDiscount
     */
    public function getCustomerDiscount()
    {
        return $this->customerDiscount;
    }

    /**
     * Set creditCardNumber
     *
     * @param string $creditCardNumber
     *
     * @return TransactionRecord
     */
    public function setCreditCardNumber($creditCardNumber)
    {
        $this->creditCardNumber = $creditCardNumber;

        return $this;
    }

    /**
     * Get creditCardNumber
     *
     * @return string
     */
    public function getCreditCardNumber()
    {
        return $this->creditCardNumber;
    }

    /**
     * Set creditCardExpirationMonth
     *
     * @param string $creditCardExpirationMonth
     *
     * @return TransactionRecord
     */
    public function setCreditCardExpirationMonth($creditCardExpirationMonth)
    {
        $this->creditCardExpirationMonth = $creditCardExpirationMonth;

        return $this;
    }

    /**
     * Get creditCardExpirationMonth
     *
     * @return string
     */
    public function getCreditCardExpirationMonth()
    {
        return $this->creditCardExpirationMonth;
    }

    /**
     * Set creditCardExpirationYear
     *
     * @param string $creditCardExpirationYear
     *
     * @return TransactionRecord
     */
    public function setCreditCardExpirationYear($creditCardExpirationYear)
    {
        $this->creditCardExpirationYear = $creditCardExpirationYear;

        return $this;
    }

    /**
     * Get creditCardExpirationYear
     *
     * @return string
     */
    public function getCreditCardExpirationYear()
    {
        return $this->creditCardExpirationYear;
    }

    /**
     * Set creditCard
     *
     * @param CreditCard $creditCard
     *
     * @return TransactionRecord
     */
    public function setCreditCard(CreditCard $creditCard = null)
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    /**
     * Get creditCard
     *
     * @return CreditCard
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @return string
     */
    public function getRefundId()
    {
        return $this->refundId;
    }

    /**
     * @param string $refundId
     *
     * @return TransactionRecord
     */
    public function setRefundId(string $refundId)
    {
        $this->refundId = $refundId;

        return $this;
    }
}
