<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Model\MindbodySession;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FromSessionService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * FromSessionService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SessionInterface       $session
     */
    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session       = $session;
    }

    /**
     * @return Customer|null
     */
    public function getCustomerFromSession()
    {
        return $this->entityManager->getRepository(Customer::class)->findOneBy(
            [
                'email'      => $this->session->get(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME),
                'merchantId' => $this->session->get(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME),
            ]
        );
    }

    /**
     * @return TransactionRecord|null
     */
    public function getTransactionRecord()
    {
        return $this->session->get(MindbodySession::TRANSACTION_RECORD_VAR_NAME);
    }

    public function setTransactionRecord(TransactionRecord $transactionRecord){
        $this->session->set(MindbodySession::TRANSACTION_RECORD_VAR_NAME, $transactionRecord);
    }

    public function removeTransactionRecord()
    {
        $this->session->remove(MindbodySession::TRANSACTION_RECORD_VAR_NAME);
    }

    public function getPaymentResponse(){
        return $this->session->get('paymentResponse');
    }

    public function removePaymentResponse()
    {
        $this->session->remove('paymentResponse');
    }

    /**
     * @param $paymentResponse
     */
    public function setPaymentResponse($paymentResponse)
    {
        $this->session->set('paymentResponse', $paymentResponse);
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->session->get(MindbodySession::MINDBODY_GRAND_TOTAL_VAR_NAME);
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->session->set(MindbodySession::MINDBODY_GRAND_TOTAL_VAR_NAME, $amount);
    }

    public function removeAmount()
    {
        return $this->session->remove(MindbodySession::MINDBODY_GRAND_TOTAL_VAR_NAME);
    }

    public function getRealMindbodyLocations()
    {
        return $this->session->get(MindbodySession::MINDBODY_REAL_LOCATIONS_VAR_NAME);
    }

    public function removeRealMindbodyLocations(){
        $this->session->remove(MindbodySession::MINDBODY_REAL_LOCATIONS_VAR_NAME);
    }

    public function setRealMindbodyLocations($locations){
        $this->session->set(MindbodySession::MINDBODY_REAL_LOCATIONS_VAR_NAME, $locations);
    }

    public function getSelectedMindbodyServiceId()
    {
        return $this->session->get(MindbodySession::MINDBODY_SELECTED_SERVICE_ID_VAR_NAME);
    }

    /**
     * @param $serviceId
     *
     * @return $this
     */
    public function setSelectedMindbodyServiceId($serviceId)
    {
        $this->session->set(MindbodySession::MINDBODY_SELECTED_SERVICE_ID_VAR_NAME, $serviceId);

        return $this;
    }

    /**
     * @return string
     */
    public function getSelectedMindbodyServiceName()
    {
        return $this->session->get(MindbodySession::MINDBODY_SELECTED_SERVICE_NAME_VAR_NAME);
    }

    /**
     * @param string $serviceName
     *
     * @return $this
     */
    public function setSelectedMindbodyServiceName($serviceName)
    {
        $this->session->set(MindbodySession::MINDBODY_SELECTED_SERVICE_NAME_VAR_NAME, $serviceName);

        return $this;
    }

    public function getDiscountCodeUsed()
    {
        return $this->session->get(MindbodySession::MINDBODY_DISCOUNT_CODE_USED_VAR_NAME);
    }

    public function getDiscountAmount()
    {
        return $this->session->get(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME, 0);
    }

    /**
     * @param double $discountAmount
     *
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->session->set(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME, $discountAmount);

        return $this;
    }

    public function removeDiscountAmount()
    {
        $this->session->remove(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME);
    }

    public function getSelectedMindbodyPaymentMethodId()
    {
        return $this->session->get(MindbodySession::MINDBODY_SELECTED_PAYMENT_METHOD_ID_VAR_NAME);
    }

    public function getCreditCardHolderName()
    {
        return $this->session->get(MindbodySession::CREDIT_CARD_HOLDER_NAME_VAR_NAME);
    }

    public function getCreditCardLastFourDigits()
    {
        return $this->session->get(MindbodySession::CREDIT_CARD_LAST_FOUR_DIGITS_VAR_NAME);
    }

    public function getUserPreferredLocationId(){
        return $this->session->get(MindbodySession::MINDBODY_USER_PREFERRED_LOCATION_ID_VAR_NAME);
    }

    public function setUserPreferredLocationId($locationId){
        $this->session->set(MindbodySession::MINDBODY_USER_PREFERRED_LOCATION_ID_VAR_NAME, $locationId);
    }

    public function setMindbodyClientGUID($GUID){
        $this->session->set(MindbodySession::MINDBODY_CLIENT_GUID_VAR_NAME, $GUID);
    }

    public function getMindbodyClientGUID(){
        return $this->session->get(MindbodySession::MINDBODY_CLIENT_GUID_VAR_NAME);
    }

    public function hasMindbodyClientGUID()
    {
        return $this->session->has(MindbodySession::MINDBODY_CLIENT_GUID_VAR_NAME);
    }

    public function getMindbodyClientID(){
        return $this->session->get(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME);
    }

    public function setMindbodyClientID($clientId){
        $this->session->set(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME, $clientId);
    }

    /**
     * @return bool
     */
    public function hasMindbodyClientID()
    {
        return $this->session->has(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME);
    }

    public function getMindbodyClientEmail(){
        return $this->session->get(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME);
    }

    public function setMindbodyClientEmail($email){
        $this->session->set(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME, $email);
    }

    public function hasMindbodyClientEmail()
    {
        return $this->session->has(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME);
    }

    /**
     * @return boolean
     */
    public function getShouldUserLocationBeUpdated(){
        return $this->session->get(MindbodySession::MINDBODY_SHOULD_CLIENT_LOCATION_BE_UPDATED_VAR_NAME);
    }

    /**
     * @param boolean $should
     */
    public function setShouldUserLocationBeUpdated($should){
        $this->session->set(MindbodySession::MINDBODY_SHOULD_CLIENT_LOCATION_BE_UPDATED_VAR_NAME, $should);
    }

    /**
     * @return string
     */
    public function getMindbodyClassType()
    {
        return $this->session->get(MindbodySession::MINDBODY_CLASS_TYPE_VAR_NAME);
    }

    /**
     * @param string $classType
     *
     * @return $this
     */
    public function setMindbodyClassType($classType): self
    {
        $this->session->set(MindbodySession::MINDBODY_CLASS_TYPE_VAR_NAME, $classType);

        return $this;
    }

    public function removeMindbodyClassType()
    {
        $this->session->remove(MindbodySession::MINDBODY_CLASS_TYPE_VAR_NAME);
    }

    /**
     * @return bool
     */
    public function hasMindbodyClassType()
    {
        return $this->session->has(MindbodySession::MINDBODY_CLASS_TYPE_VAR_NAME);
    }

    /**
     * @return string
     */
    public function getMindbodyClassTeacherName()
    {
        return $this->session->get(MindbodySession::MINDBODY_CLASS_TEACHER_NAME_VAR_NAME);
    }

    /**
     * @param string $teacherName
     *
     * @return $this
     */
    public function setMindbodyClassTeacherName($teacherName)
    {
        $this->session->set(MindbodySession::MINDBODY_CLASS_TEACHER_NAME_VAR_NAME, $teacherName);

        return $this;
    }

    /**
     * @return string
     */
    public function getMindbodyClassName()
    {
        return $this->session->get(MindbodySession::MINDBODY_CLASS_NAME_VAR_NAME);
    }

    /**
     * @param string $clasName
     *
     * @return $this
     */
    public function setMindbodyClassName($clasName)
    {
        $this->session->set(MindbodySession::MINDBODY_CLASS_NAME_VAR_NAME, $clasName);

        return $this;
    }

    /**
     * @return string
     */
    public function getMindbodyClassStartTime()
    {
        return $this->session->get(MindbodySession::MINDBODY_CLASS_START_TIME_VAR_NAME);
    }

    /**
     * @param string $startTime
     *
     * @return $this
     */
    public function setMindbodyClassStartTime($startTime)
    {
        $this->session->set(MindbodySession::MINDBODY_CLASS_START_TIME_VAR_NAME, $startTime);

        return $this;
    }

    /**
     * @return string
     */
    public function getMindbodyClassEndTime()
    {
        return $this->session->get(MindbodySession::MINDBODY_CLASS_END_TIME_VAR_NAME);
    }

    /**
     * @param string $endTime
     *
     * @return $this
     */
    public function setMindbodyClassEndTime($endTime)
    {
        $this->session->set(MindbodySession::MINDBODY_CLASS_END_TIME_VAR_NAME, $endTime);

        return $this;
    }

    /**
     * @return string
     */
    public function getMindbodyClassId()
    {
        return $this->session->get(MindbodySession::MINDBODY_CLASS_ID_VAR_NAME);
    }

    /**
     * @param string|int $classId
     *
     * @return $this
     */
    public function setMindbodyClassId($classId)
    {
        $this->session->set(MindbodySession::MINDBODY_CLASS_ID_VAR_NAME, $classId);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMindbodyClassId()
    {
        return $this->session->has(MindbodySession::MINDBODY_CLASS_ID_VAR_NAME);
    }

    /**
     * @return string
     */
    public function getMindbodyClientCurrentServiceId()
    {
        return $this->session->get(MindbodySession::MINDBODY_CLIENT_CURRENT_SERVICE_ID);
    }

    /**
     * @return bool
     */
    public function hasMindbodyClientCurrentServiceId()
    {
        return $this->session->has(MindbodySession::MINDBODY_CLIENT_CURRENT_SERVICE_ID);
    }

    /**
     * @param string|int $clientCurrentServiceId
     *
     * @return $this
     */
    public function setMindbodyClientCurrentServiceId($clientCurrentServiceId)
    {
        $this->session->set(MindbodySession::MINDBODY_CLIENT_CURRENT_SERVICE_ID, $clientCurrentServiceId);

        return $this;
    }

    public function removeMindbodyClientCurrentServiceId()
    {
        $this->session->remove(MindbodySession::MINDBODY_CLIENT_CURRENT_SERVICE_ID);
    }

    public function destroyMindbodySession(){
        $this->session->remove(MindbodySession::MINDBODY_CLIENT_GUID_VAR_NAME);
        $this->session->remove(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME);
        $this->session->remove(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME);
        //TODO: Check if the following is needed
        $this->session->remove('mindbody_client');
    }

}