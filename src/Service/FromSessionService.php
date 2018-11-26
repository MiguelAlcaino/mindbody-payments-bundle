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
        return $this->session->get('transactionRecord');
    }

    public function setTransactionRecord(TransactionRecord $transactionRecord){
        $this->session->set('transactionRecord', $transactionRecord);
    }

    public function getPaymentResponse(){
        return $this->session->get('paymentResponse');
    }

    public function getAmount()
    {
        return $this->session->get(MindbodySession::MINDBODY_GRAND_TOTAL_VAR_NAME);
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

    public function getDiscountCodeUsed()
    {
        return $this->session->get(MindbodySession::MINDBODY_DISCOUNT_CODE_USED_VAR_NAME);
    }

    public function getDiscountAmount()
    {
        return $this->session->get(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME, 0);
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

    public function getMindbodyClientID(){
        return $this->session->get(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME);
    }

    public function setMindbodyClientID($clientId){
        $this->session->set(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME, $clientId);
    }

    public function getMindbodyClientEmail(){
        return $this->session->get(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME);
    }

    public function setMindbodyClientEmail($email){
        $this->session->set(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME, $email);
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

}