<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\Customer;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Session\FromSessionService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use Symfony\Component\Form\FormInterface;

class CustomerFillerService
{
    /**
     * @var FromSessionService
     */
    private $fromSessionService;

    /**
     * @var MindbodyService
     */
    private $mindbodyService;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * CustomerFillerService constructor.
     *
     * @param FromSessionService     $fromSessionService
     * @param MindbodyService        $mindbodyService
     * @param EntityManagerInterface $manager
     */
    public function __construct(FromSessionService $fromSessionService, MindbodyService $mindbodyService, EntityManagerInterface $manager)
    {
        $this->fromSessionService = $fromSessionService;
        $this->mindbodyService    = $mindbodyService;
        $this->manager            = $manager;
    }

    /**
     * Updates Customer data after a successful Mindbody Login
     * USED IN LOGIN PROCESS
     *
     * @param $validateLogin
     *
     * @return Customer|object|null
     */
    public function upsertCustomerAfterLogin($validateLogin)
    {
        $clients = $this->mindbodyService->getClients(
            [
                sprintf($validateLogin['ValidateLoginResult']['Client']['ID']),
            ]
        );

        $systemCustomer = $this->manager->getRepository(Customer::class)->findOneBy(
            [
                'email' => $validateLogin['ValidateLoginResult']['Client']['Email'],
            ]
        );

        if (is_null($systemCustomer)) {
            $systemCustomer = (new Customer());
        }

        $systemCustomer
            ->setMerchantId($validateLogin['ValidateLoginResult']['Client']['ID'])
            ->setFirstName($validateLogin['ValidateLoginResult']['Client']['FirstName'])
            ->setLastName($validateLogin['ValidateLoginResult']['Client']['LastName'])
            ->setEmail($validateLogin['ValidateLoginResult']['Client']['Email']);

        if (!empty($clients['GetClientsResult']['Clients']['Client']['Country'])) {
            $systemCustomer
                ->setUserCountry($clients['GetClientsResult']['Clients']['Client']['Country']);
        } else {
            $systemCustomer->setUserCountry(null);
        }

        if (!empty($clients['GetClientsResult']['Clients']['Client']['State'])) {
            $systemCustomer->setUserState($clients['GetClientsResult']['Clients']['Client']['State']);
        } else {
            $systemCustomer->setUserState(null);
        }

        if (!empty($clients['GetClientsResult']['Clients']['Client']['City'])) {
            $systemCustomer->setUserCity($clients['GetClientsResult']['Clients']['Client']['City']);
        } else {
            $systemCustomer->setUserCity(null);
        }

        $this->shouldClientLocationBeUpdated($clients);

        return $systemCustomer;
    }

    /**
     * Whether or not a user preferred location should be updated
     *
     * @param $clients
     *
     * @return bool
     */
    public function shouldClientLocationBeUpdated($clients)
    {
        if (!array_key_exists('HomeLocation', $clients['GetClientsResult']['Clients']['Client'])) {
            $locations = $this->mindbodyService->getRealLocations();
            $this->fromSessionService->setRealMindbodyLocations($locations);

            return true;
        } else {
            $this->fromSessionService->setUserPreferredLocationId($clients['GetClientsResult']['Clients']['Client']['HomeLocation']['ID']);

            return false;
        }
    }

    /**
     * @param FormInterface          $form
     * @param Customer               $systemCustomer
     * @param TransactionRecord|null $transactionRecord
     */
    public function updateCustomerLocationAndCountryAndStateAndCity(FormInterface $form, Customer $systemCustomer, TransactionRecord $transactionRecord = null){
        if ($form->has('city')) {
            $systemCustomer->setUserCity($form->get('city')->getData());
            $systemCustomer->setUserState($form->get('state')->getData());
            $systemCustomer->setUserCountry($form->get('country')->getData());
        }

        //Updating Mindbody client's location
        if ($form->has('preferredLocations')) {
            $preferredLocationId = $form->get('preferredLocations')->getData();
            try {
                $this->mindbodyService->updateClientLocation(
                    $systemCustomer->getMerchantId(),
                    $form->get('preferredLocations')->getData(),
                    $systemCustomer->getUserCountry(),
                    $systemCustomer->getUserState(),
                    $systemCustomer->getUserCity()
                );
                $transactionRecord->setUserLocationUpdated(true);
            } catch (\Exception $exception) {
                $transactionRecord->setUserLocationUpdatedError(true);
            }
        }else{
            $preferredLocationId = $this->fromSessionService->getUserPreferredLocationId();
        }
        $transactionRecord->setUserPreferredLocation($this->mindbodyService->getLocationNameById($preferredLocationId));
    }

    public function fillDocumentInformation(FormInterface $form, Customer $systemCustomer){
        $systemCustomer
            ->setDocumentType($form->get('documentType')->getData())
            ->setDocumentNumber($form->get('documentNumber')->getData());
        return $systemCustomer;
    }

}