<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CreditCard;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\CreditCardType;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\LoginType;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\CountryAndCitiesService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package MiguelAlcaino\MindbodyPaymentsBundle\Controller
 * @Route("mindbody")
 */
class DefaultController extends AbstractController
{
    /**
     * @param Request $request
     * @return array|Response
     * @Route("/", name="mindbody_default_index", methods={"GET"})
     *
     */
    public function indexAction(Request $request)
    {
        $errorMessage = $request->getSession()->get('errorMessage');
        $request->getSession()->invalidate();

        return [
            'errorMessage' => $errorMessage,
        ];
    }

    /**
     * @Route("/services-for-location", name="default_services_for_location_ajax")
     * @param Request $request
     * @param MindBodyService $mindBodyService
     *
     * @return JsonResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getServicesForLocationAjaxAction(Request $request, MindBodyService $mindBodyService)
    {
        $locationName = $request->query->get('locationName');
        $services = $mindBodyService->getFormattedServices(false);

        //Removing prices with 0
        foreach ($services as $key => $service) {
            if ($service['price'] == 0) {
                unset($services[$key]);
            }
        }

        $locationServices = [];

        //TODO: Put this logic in the db

        $locationServices['medellin'] = $services;


        return new JsonResponse([
            'view' => $this->renderView('@MiguelAlcainoMindbodyPayments/Default/servicesPartial.html.twig', [
                'services' => $locationServices[$locationName]
            ])
        ]);
    }


    /**
     * @param Request $request
     * @param MindBodyService $mindBodyService
     * @return Response|array
     * @Route("/login-mindbody", name="mindbody_customer_login")
     */
    public function loginCustomerAction(Request $request, MindBodyService $mindBodyService){

        $request->getSession()->set('mindbody_service', $request->query->get('service'));

        $form = $this->createForm(LoginType::class, null, [
            'method' => 'POST'
        ]);

        $arrayToReturn = [];

        $form->handleRequest($request);

        $arrayToReturn['form'] = $form->createView();
        if($form->isSubmitted() && $form->isValid()){
            try {
                $validateLogin = $mindBodyService->validateLogin(
                    $form->get('email')->getData(),
                    $form->get('password')->getData()
                );

                $clients = $mindBodyService->getClients([
                    sprintf($validateLogin['ValidateLoginResult']['Client']['ID'])
                ]);

                $locations = $mindBodyService->getRealLocations();

                $manager = $this->getDoctrine()->getManager();

                $systemCustomer = $manager->getRepository(Customer::class)->findOneBy(
                    [
                        'email' => $validateLogin['ValidateLoginResult']['Client']['Email']
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

                $manager->persist($systemCustomer);

                $manager->flush();

                $request->getSession()->set('mindbody_client_guid', $validateLogin['ValidateLoginResult']['GUID']);
                $request->getSession()->set('mindbody_client_ID', $systemCustomer->getMerchantId());
                $request->getSession()->set('mindbody_client_email', $systemCustomer->getEmail());
                $request->getSession()->set('mindbody_client', $clients);

                if (!array_key_exists('HomeLocation', $clients['GetClientsResult']['Clients']['Client'])) {
                    $request->getSession()->set('locations', $locations);
                } else {
                    $request->getSession()->set('preferredLocationId', $clients['GetClientsResult']['Clients']['Client']['HomeLocation']['ID']);
                }

                if($request->getSession()->has('referral')){
                    $referral = $request->getSession()->get('referral');
                    return $this->redirect($referral);
                }else{
                    if($this->container->hasParameter('login_success_route')){
                        return $this->redirectToRoute($this->getParameter('login_success_route'));
                    }else{
                        return $this->redirectToRoute('select_payment_method');
                    }

                }
            } catch (NotValidLoginException $exception) {
                $arrayToReturn['errorMessage'] = 'Incorrect email or password. Please try again.';

                return $this->renderLoginForm($arrayToReturn);
            }
        }else{
            return $this->renderLoginForm($arrayToReturn);

        }
    }

    /**
     * Returns a rendered view of a Login form
     * @param array $viewParams
     *
     * @return Response
     */
    private function renderLoginForm(array $viewParams){
        if($this->container->hasParameter('login_tempate')){
            return $this->render($this->getParameter('login_template'), $viewParams);
        }else{
            return $this->render('@MindBodyPayments/Default/login.html.twig', $viewParams);
        }
    }

    /**
     * @param Request $request
     * @param MindBodyService $mindBodyService
     * @return array|Response
     * @throws \Exception
     * @Route("/select-payment-method", name="select_payment_method")
     * @Template("@MindBodyPayments/Tpaga/payment.html.twig")
     */
    public function fillPaymentMethodAction(Request $request, MindBodyService $mindBodyService)
    {
        $manager = $this->getDoctrine()->getManager();
        $config = new Configuration();
        $config->setHost($this->getParameter('tpaga_api_host'));
        $apiClient = new ApiClient($config);
        $systemCustomer = $manager->getRepository(Customer::class)->findOneBy([
            'email' => $request->getSession()->get('mindbody_client_email'),
            'merchantId' => $request->getSession()->get('mindbody_client_ID')
        ]);

        //The system customer should always exist at this point
        if (!is_null($systemCustomer)) {

            $config->setUsername($this->getParameter('tpaga_private_key'));

            $customerApi = new CustomerApi($apiClient);

            if (is_null($systemCustomer->getPaymentGatewayId())) {
                //Costumer doing payments for first time
                $this->get('logger')->info('Adding new customer');
                $tPagaCustomer = (new Customer())
                    ->setEmail($systemCustomer->getEmail())
                    ->setFirstName($systemCustomer->getFirstName())
                    ->setLastName($systemCustomer->getLastName());

                try {

                    $persistedCustomer = $customerApi->createCustomer($tPagaCustomer);

                    $systemCustomer->setPaymentGatewayId($persistedCustomer->getId());

                    $manager->persist($systemCustomer);

                    $manager->flush();

                } catch (\Exception $e) {
                    return [
                        'errorMessage' => $e->getMessage()
                    ];
                }
            } else {
                //Costumer has made payments before
                try {
                    $persistedCustomer = $customerApi->getCustomerById($systemCustomer->getPaymentGatewayId());

                } catch (\Exception $exception) {
                    //Error recovering customer data from TPAGA
                    throw new \Exception('Error recovering data from tpaga');
                }
            }

            $formattedCustomPaymentMethods = $mindBodyService->getFormattedCustomPaymentMethods();

            $checkoutShoppingCartRequest = $mindBodyService->calculateShoppingCart(
                $systemCustomer->getMerchantId(),
                $request->getSession()->get('mindbody_service')
            );


            if ($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ErrorCode'] === 905) {
                $request->getSession()->set('errorMessage', 'Estás intentando comprar un servicio que es sólo para nuevos usuarios. Nuestros registros indican que ya has comprado antes. Por favor selecciona otro servicio.');
                return $this->redirectToRoute('default_index');
            }

            $form = $this->createForm(CreditCardType::class, null, [
                'action' => $this->generateUrl('tpaga_process_credit_card'),
                'method' => 'POST',
                'customPaymentMethods' => $formattedCustomPaymentMethods,
                'locations' => $request->getSession()->get('locations'),
                'customer' => $systemCustomer
            ]);

            $creditCards = $systemCustomer->getCreditCards();

            /** @var CreditCard $creditCard */
            foreach ($creditCards as $creditCard) {
                $uniqueId = uniqid();
                $creditCard->setUrlTemporalToken($uniqueId);
                $request->getSession()->set('credit_card_' . $creditCard->getId() . '_url_token', $uniqueId);
            }


            $request->getSession()->set('grandTotal',$checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal']);
            $request->getSession()->set('itemName', $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['CartItems']['CartItem']['Item']['Name']);
            return [
                'persistedCustomer' => $persistedCustomer,
                'form' => $form->createView(),
                'creditCards' => $creditCards,
                'checkoutShoppingCart' => $checkoutShoppingCartRequest
            ];

        } else {
            throw new \Exception('You should not be here.');
        }
    }

    /**
     * @param Request $request
     * @param MindBodyService $mindBodyService
     * @return array|Response
     * @Route("/credit-card/process-credit-card", name="tpaga_process_credit_card")
     * @Template("@MindBodyPayments/Default/cardProcessed.html.twig")
     */
    public function processCreditCardAction(Request $request, MindBodyService $mindBodyService)
    {
        $manager = $this->getDoctrine()->getManager();
        $config = new Configuration();
        $config->setHost($this->getParameter('tpaga_api_host'));

        $apiClient = new ApiClient($config);

        $config->setUsername($this->getParameter('tpaga_public_key'));
        $formattedCustomPaymentMethods = [];

        /** @var \MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer $systemCustomer */
        $systemCustomer = $manager->getRepository(\MindBodyPaymentsBundle\Entity\Customer::class)->findOneBy([
            'email' => $request->getSession()->get('mindbody_client_email'),
            'merchantId' => $request->getSession()->get('mindbody_client_ID')
        ]);

        if (is_null($systemCustomer)) {
            return $this->redirectToRoute('select_payment_method');
        }

        try {
            $mb = $mindBodyService->getMindBody();

            $customPaymentMethods = $mb->GetCustomPaymentMethods();
            if (array_key_exists('Name', $customPaymentMethods['GetCustomPaymentMethodsResult']['PaymentMethods']['CustomPaymentInfo'])) {
                $customPaymentMethod = $customPaymentMethods['GetCustomPaymentMethodsResult']['PaymentMethods']['CustomPaymentInfo'];
                $formattedCustomPaymentMethods[$customPaymentMethod['Name']] = $customPaymentMethod['ID'];
            } else {
                foreach ($customPaymentMethods['GetCustomPaymentMethodsResult']['PaymentMethods']['CustomPaymentInfo'] as $customPaymentMethod) {
                    $formattedCustomPaymentMethods[$customPaymentMethod['Name']] = $customPaymentMethod['ID'];
                }
            }
        } catch (\Exception $exception) {
            $this->addFlash('errorMessage', 'Ha habido un error al comunicarse con el gimnasio. Por favor intenta otra vez');

            return $this->redirectToRoute('select_payment_method');
        }
        if ($request->getSession()->get('limitedDiscountEnabled')) {
            $customerDiscount = $manager->getRepository(CustomerDiscount::class)->getValidCustomerDiscount(
                $systemCustomer,
                $request->getSession()->get('limitedDiscountCode'),
                new \DateTime()
            );

            $form = $this->createForm(CreditCardWithLimitedDiscountType::class, null, [
                'action' => $this->generateUrl('tpaga_process_credit_card'),
                'method' => 'POST',
                'customPaymentMethods' => $formattedCustomPaymentMethods,
                'locations' => $request->getSession()->get('locations'),
                'customer' => $systemCustomer,
                'products' => $customerDiscount->getDiscount()->getProducts(),
                'mainProduct' => $customerDiscount->getDiscount()->getMainProduct()
            ]);
        } else {
            $form = $this->createForm(CreditCardType::class, null, [
                'action' => $this->generateUrl('tpaga_process_credit_card'),
                'method' => 'POST',
                'customPaymentMethods' => $formattedCustomPaymentMethods,
                'locations' => $request->getSession()->get('locations'),
                'customer' => $systemCustomer
            ]);
        }


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $month = $form->get('month')->getData();
            $year = $form->get('year')->getData();

            $expirationDate = \DateTime::createFromFormat('Y-m-d H:i:s', $year . '-' . $month . '-01 00:00:00');
            $currentMonthYear = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m') . '-01 00:00:00');

            $tenYearsMoreDate = \DateTime::createFromFormat('Y-m-d H:i:s', (date('Y') + 10) . '-' . date('m') . '-01 00:00:00');

            if ($expirationDate->getTimestamp() < $currentMonthYear->getTimestamp()) {
                return $this->render('@MindBodyPayments/Tpaga/payment.html.twig', [
                    'form' => $form->createView(),
                    'expirationDateErrorMessage' => 'La fecha de expiración es anterior al mes actual.'
                ]);
            } else if ($expirationDate->getTimestamp() > $tenYearsMoreDate->getTimestamp()) {
                return $this->render('@MindBodyPayments/Tpaga/payment.html.twig', [
                    'form' => $form->createView(),
                    'expirationDateErrorMessage' => 'La fecha de expiración supera 10 años desde la fecha actual.'
                ]);
            }

            //Updating client's location if necessary
            if ($form->has('preferredLocations')) {
                $request->getSession()->set('preferredLocationId', $form->get('preferredLocations')->getData());
                $request->getSession()->set('updateUserLocation', true);
            } else {
                $request->getSession()->set('updateUserLocation', false);
            }

            if ($form->has('city')) {
                $request->getSession()->set('userCity', $form->get('city')->getData());
                $request->getSession()->set('userState', $form->get('state')->getData());
                $request->getSession()->set('userCountry', $form->get('country')->getData());
                $systemCustomer->setUserCity($form->get('city')->getData());
                $systemCustomer->setUserState($form->get('state')->getData());
                $systemCustomer->setUserCountry($form->get('country')->getData());
            }

            $systemCustomer
                ->setDocumentType($form->get('documentType')->getData())
                ->setDocumentNumber($form->get('documentNumber')->getData());

            $manager->persist($systemCustomer);
            $manager->flush();

            $ccToken = (new CreditCardToken())
                ->setCardHolderName($form->get('cardHolderName')->getData())
                ->setPrimaryAccountNumber($form->get('cardNumber')->getData())
                ->setExpirationMonth($form->get('month')->getData())
                ->setExpirationYear($form->get('year')->getData())
                ->setCvc($form->get('cvc')->getData());

            $request->getSession()->set('mindbody_payment_method_id', $form->get('paymentMethods')->getData());
            $request->getSession()->set('payment_gateway_installments', $form->get('installments')->getData());

            $tokenizeApi = new TokenizeApi($apiClient);

            try {
                $creditCardToken = $tokenizeApi->tokenizeCreditCard($ccToken);

                $config->setUsername($this->getParameter('tpaga_private_key'));
                $apiClient = new ApiClient($config);
                $tokenApi = new TokenApi($apiClient);

                if (!is_null($systemCustomer)) {
                    $addedCreditCard = $tokenApi->addCreditCardToken($systemCustomer->getPaymentGatewayId(), $creditCardToken);
                        $request->getSession()->set('added_credit_card_token', $addedCreditCard->getId());
                    $request->getSession()->set('added_credit_card_holder_name', $addedCreditCard->getCardHolderName());
                    $request->getSession()->set('added_credit_card_last_four_digits', $addedCreditCard->getLastFour());
                        return $this->redirectToRoute('tpaga_payment_with_new_card');
                } else {
                    throw new \Exception('You should not be here');
                }

            } catch (\Exception $exception) {
                //TODO: Save error log in the database
                return $this->render('@MindBodyPayments/Tpaga/payment.html.twig', [
                    'form' => $form->createView(),
                    'errorMessage' => 'Ha ocurrido un error inesperado. Por favor intenta otra vez.'
                ]);
            }

        } else {

            $systemCustomer = $manager->getRepository(\MindBodyPaymentsBundle\Entity\Customer::class)->findOneBy([
                'email' => $request->getSession()->get('mindbody_client_email'),
                'merchantId' => $request->getSession()->get('mindbody_client_ID')
            ]);

            $creditCards = [];
            if (!is_null($systemCustomer)) {
                $creditCards = $systemCustomer->getCreditCards();

                /** @var CreditCard $creditCard */
                foreach ($creditCards as $creditCard) {
                    $uniqueId = uniqid();
                    $creditCard->setUrlTemporalToken($uniqueId);
                    $request->getSession()->set('credit_card_' . $creditCard->getId() . '_url_token', $uniqueId);
                }
            }

            return $this->render('@MindBodyPayments/Tpaga/payment.html.twig', [
                'form' => $form->createView(),
                'creditCards' => $creditCards
            ]);
        }
    }

    /**
     * @param Request $request
     * @return array|Response
     * @Route("/tpaga/payment", name="tpaga_payment_with_new_card")
     */
    public function paymentWithNewCardAction(Request $request, MindBodyService $mindBodyService, CountryAndCitiesService $countryAndCitiesService)
    {
        $creditCardToken = $request->getSession()->get('added_credit_card_token');
        $config = new Configuration();
        $config->setHost($this->getParameter('tpaga_api_host'));

        $apiClient = new ApiClient($config);

        $config->setUsername($this->getParameter('tpaga_private_key'));

        $taxAmount = 0;
        $installments = $request->getSession()->get('payment_gateway_installments');

        $manager = $this->getDoctrine()->getManager();
        $systemCustomer = $manager->getRepository(\MindBodyPaymentsBundle\Entity\Customer::class)
            ->findOneBy([
                'merchantId' => $request->getSession()->get('mindbody_client_ID')
            ]);

        $transactionRecord = new TransactionRecord();

        //Making the payment in Tpaga
        $charge = (new CreditCardCharge())
            ->setAmount($request->getSession()->get('grandTotal'))
            ->setTaxAmount($taxAmount)
            ->setCurrency($this->getParameter('currency'))
            ->setCreditCard($creditCardToken)
            ->setInstallments($installments)
            ->setDescription('Compra de ' . $request->getSession()->get('itemName') . ' por ' . $systemCustomer->getFirstName() . ' ' . $systemCustomer->getLastName() . ' en Cyglo.');

        $chargeApi = new ChargeApi($apiClient);

        try {
            /** @var CreditCardCharge $paymentResponse */
            $paymentResponse = $chargeApi->addCreditCardCharge($charge);

            $originalPaymentResponse = $chargeApi->getApiResponse();

            if (!$paymentResponse->getPaid()) {
                $this->addFlash('errorMessage', 'El cargo de la tarjeta de pago no ha sido aceptado.');

                return $this->redirectToRoute('select_payment_method');
            }

        } catch (ApiException $exception) {
            if (isset($exception->getResponseBody()->errorCode)) {
                switch ($exception->getResponseBody()->errorCode) {
                    case '61': //Exceed maximum amount
                        break;
                    case '51': //Not enough funds
                        break;
                    case '54': //Rejected, expired card
                        break;
                    case '14': //Rejected, card's stat is not valid
                        break;
                    case '56': //Rejected, CAF not found
                        break;
                    case '65': //Rejected, limit of uses exceeded
                        break;
                    case '62': //Rejected, restricted card
                        break;
                    case '2': //Rejected, call your bank entity
                        break;
                    case '6': //Rejected, transaction could not be processed
                        break;
                    case '5': //Rejected, it can be blocked card or timeout
                        break;
                    case '57': //Rejected, transaction not allowed for this card
                        break;
                    case '4': //Rejected, retain card
                        break;
                    case '36': //Rejected, retain card
                        break;
                    case '43': //Rejected, state of the file CAF
                        break;
                    case '41': //Rejected, card is stolen or lost
                        break;
                    case 'T8': //Rejected, the link is broken
                        break;
                    case '91': //Rejected, it's not possible to authorize
                        break;
                    case 'N0': //Rejected, it's not possible to authorize
                        break;
                    case '2310': //Rejected, Expiring date is incorrect
                        break;
                    case '1122': //Rejected, BIN is not registered in the system
                        break;
                    case '1123': //Rejected, BIN does not belong to the franchise
                        break;
                    case '5000': //Rejected, Certificate does not belong to the business' ID sent
                        break;
                    case '5003': //Rejected, Certificate used is not linked to the terminal
                        break;
                    case '2305': //Rejected, Business is not active
                        break;
                    case '2402': //Rejected, Error processing authorization
                        break;
                    case '1101': //Rejected, Error communication problem
                        break;
                    case '6001': //Rejected, Error 6001
                        break;
                    case '9999': //Rejected, Cannot open connection
                        break;
                }

                $this->addFlash('errorMessage', $exception->getResponseBody()->errorMessage);
            } else {
                $this->addFlash('errorMessage', $exception->getMessage());
            }

            return $this->redirectToRoute('select_payment_method');
        }

        $transactionRecord
            ->setCreditCardHolderName($request->getSession()->get('added_credit_card_holder_name'))
            ->setCreditCardLastFourDigits($request->getSession()->get('added_credit_card_last_four_digits'))
            ->setCustomer($systemCustomer)
            ->setUserPreferredLocation($mindBodyService->getLocationNameById($request->getSession()->get('preferredLocationId')))
            ->setUserCountry($systemCustomer->getUserCountry())
            ->setUserState($countryAndCitiesService->getCityNameByCode($systemCustomer->getUserState()))
            ->setUserCity($systemCustomer->getUserCity())
            ->setDocumentType($systemCustomer->getDocumentType())
            ->setDocumentNumber($systemCustomer->getDocumentNumber())
            ->setPaymentGatewayResponse(json_encode($originalPaymentResponse))
            ->setPaymentGatewayFee($originalPaymentResponse->tpagaFeeAmount)
            ->setStatus($originalPaymentResponse->transactionInfo->status)
            ->setAuthorizationCode($originalPaymentResponse->transactionInfo->authorizationCode)
            ->setInstallments($installments)
            ->setTaxAmount($taxAmount)
            ->setDiscountCode($request->getSession()->get('discountCode'))
            ->setDiscountAmount($request->getSession()->get('discountAmount'))
            ->setPaymentTransaction($paymentResponse->getPaymentTransaction())
            ->setCreditCardChargeId($paymentResponse->getId())
            ->setMindbodyPaymentMethodId($request->getSession()->get('mindbody_payment_method_id'))
            ->setPreAmount($request->getSession()->get('grandTotal'))
            ->setServiceId($request->getSession()->get('mindbody_service'));


        $transactionRecord = $mindBodyService->makePurchase(
            $transactionRecord,
            $request->getSession()->get('mindbody_client_ID'),
            $request->getSession()->get('mindbody_service'),
            $request->getSession()->get('mindbody_payment_method_id'),
            $request->getSession()->get('grandTotal'),
            $request->getSession()->get('discountCode'),
            $request->getSession()->get('discountAmount', 0)
        );

        //Updating Mindbody client's location
        if ($request->getSession()->get('updateUserLocation') || $request->getSession()->has('userCity')) {
            try {
                $mindBodyService->updateClientLocation(
                    $request->getSession()->get('mindbody_client_ID'),
                    $request->getSession()->get('preferredLocationId'),
                    $request->getSession()->get('userCountry'),
                    $request->getSession()->get('userState'),
                    $request->getSession()->get('userCity')
                );
                $transactionRecord->setUserLocationUpdated(true);
            } catch (\Exception $exception) {
                $transactionRecord->setUserLocationUpdatedError(true);
            }
        }

        if ($request->getSession()->get('limitedDiscountEnabled') === true) {
            $customerDiscount = $manager->getRepository(CustomerDiscount::class)->find($request->getSession()->get('customerDiscountId'));
            if (!$customerDiscount->getIsUsed()) {
                $transactionRecord
                    ->setCustomerDiscount($customerDiscount)
                    ->setDiscountAmount($request->getSession()->get('discountAmount'));
                $customerDiscount->setIsUsed(true);

                $manager->persist($customerDiscount);
            }
        }

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($transactionRecord);
        $manager->flush();

        if ($transactionRecord->getMindbodyCheckoutFail()) {
            $mailer = $this->get('mailer');
            $message = (new \Swift_Message('Una transaccion necesita tu intervencion'))
                ->setFrom($this->getParameter('mailer_user'))
                ->setTo($this->getParameter('admin_email'))
                ->setBody(
                    $this->renderView('@MindBodyPayments/Mail/missingMindbodyRecordEmail.html.twig', [
                        'transactionRecord' => $transactionRecord
                    ]),
                    'text/html'
                );

            $mailer->send($message);
        }


        $request->getSession()->set('paymentResponse', $paymentResponse);
        $request->getSession()->set('successfulPaymentDatetime', new \DateTime('now'));
        $request->getSession()->set('transactionRecord', $transactionRecord);

        return $this->redirectToRoute('mindbody_payment_response');
    }

    /**
     * @Route("/sendmail")
     */
    public function sendEmailAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $transactionRecord = $manager->getRepository(TransactionRecord::class)->find(82);

        return $this->render('@MindBodyPayments/Mail/mindbodyPurchaseFixed.html.twig', ['transactionRecord' => $transactionRecord]);
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/payment-response", name="mindbody_payment_response")
     * @Template("@MindBodyPayments/Default/paymentResponse.html.twig")
     */
    public function paymentResponseAction(Request $request)
    {
        //Removing locations from session
        $request->getSession()->remove('locations');

        $paymentResponse = $request->getSession()->get('paymentResponse');
        /** @var TransactionRecord $transactionRecord */
        $transactionRecord = $request->getSession()->get('transactionRecord');
        return [
            'paymentResponse' => $paymentResponse,
            'transactionRecord' => $transactionRecord,
            'discountCode' => $request->getSession()->get('discountCode'),
            'grandTotal' => $request->getSession()->get('grandTotal')
        ];
    }

    /**
     * @param Request         $request
     * @param string          $code
     * @param MindBodyService $mindBodyService
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @Route("/limited-discount/{code}", name="mindbody_limited_discount_use")
     * @Template("@MindBodyPayments/Default/login.html.twig")
     */
    public function useLimitedDiscountAction(Request $request, string $code, MindBodyService $mindBodyService)
    {
        $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(LoginType::class, null, [
            'method' => 'POST'
        ]);

        $arrayToReturn = [];
        $mb = $mindBodyService->getMindBody();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $validateLogin = $mb->ValidateLogin(array(
                'Username' => $form->get('email')->getData(),
                'Password' => $form->get('password')->getData()
            ));

            if (!empty($validateLogin['ValidateLoginResult']['GUID'])) {

                $systemCustomer = $manager->getRepository(\MindBodyPaymentsBundle\Entity\Customer::class)->findOneBy(
                    [
                        'email' => $validateLogin['ValidateLoginResult']['Client']['Email']
                    ]
                );

                $customerDiscount = $manager->getRepository(CustomerDiscount::class)->getValidCustomerDiscount($systemCustomer, $code, new \DateTime());

                if (is_null($customerDiscount)) {
                    $request->getSession()->set('errorMessage', 'El descuento que estás intentando usar ya no es válido');
                    return $this->redirectToRoute('default_index');
                } else {
                    $formattedCustomPaymentMethods = $mindBodyService->getFormattedCustomPaymentMethods();

                    $clients = $mindBodyService->getClients([
                        sprintf($validateLogin['ValidateLoginResult']['Client']['ID'])
                    ]);

                    $locations = $mindBodyService->getRealLocations();

                    if (!array_key_exists('HomeLocation', $clients['GetClientsResult']['Clients']['Client'])) {
                        $request->getSession()->set('locations', $locations);
                    } else {
                        $request->getSession()->set('preferredLocationId', $clients['GetClientsResult']['Clients']['Client']['HomeLocation']['ID']);
                    }

                    $form = $this->createForm(CreditCardWithLimitedDiscountType::class, null, [
                        'action' => $this->generateUrl('tpaga_process_credit_card'),
                        'method' => 'POST',
                        'customPaymentMethods' => $formattedCustomPaymentMethods,
                        'locations' => $request->getSession()->get('locations'),
                        'customer' => $systemCustomer,
                        'products' => $customerDiscount->getDiscount()->getProducts(),
                        'mainProduct' => $customerDiscount->getDiscount()->getMainProduct()
                    ]);

                    $form->handleRequest($request);
                    $discountAmount = $customerDiscount->getDiscount()->getMainProduct()->getPrice() * ($customerDiscount->getDiscount()->getDiscountPercentage() / 100);
                    $discountedPrice = $customerDiscount->getDiscount()->getMainProduct()->getPrice() - $discountAmount;

                    $request->getSession()->set('grandTotal', $discountedPrice);
                    $request->getSession()->set('discountAmount', $discountAmount);
                    $request->getSession()->set('limitedDiscountEnabled', true);
                    $request->getSession()->set('limitedDiscountCode', $code);
                    $request->getSession()->set('customerDiscountId', $customerDiscount->getId());
                    $request->getSession()->set('mindbody_service', $customerDiscount->getDiscount()->getMainProduct()->getMerchantId());
                    $request->getSession()->set('mindbody_client_ID', $systemCustomer->getMerchantId());

                    $request->getSession()->set('mindbody_client_email', $systemCustomer->getEmail());


                    return $this->render('@MindBodyPayments/Tpaga/limitedDiscountPayment.html.twig', [
                        'customerDiscount' => $customerDiscount,
                        'customer' => $systemCustomer,
                        'form' => $form->createView(),
                        'discountAmount' => $discountAmount,
                        'discountedPrice' => $discountedPrice
                    ]);
                }
            } else {
                $arrayToReturn['errorMessage'] = 'El email o contraseña usados no fueron encontrados. Por favor intenta de nuevo.';
            }
        }

        $arrayToReturn['form'] = $form->createView();

        return $arrayToReturn;
    }

    /**
     * @param Request $request
     * @param MindBodyService $mindBodyService
     * @return Response
     * @Route("/apply-discount", name="apply_discount", methods={"POST"})
     *
     * @return JsonResponse
     * @throws \MiguelAlcaino\MindbodyPaymentsBundle\Exception\InvalidItemInSHoppingCartException
     */
    public function applyDiscountCodeAction(Request $request, MindBodyService $mindBodyService)
    {
        $discountCode = $request->request->get('discountCode');
        if (empty($discountCode)) {
            throw new \Exception('Not valid discount code');
        }
        $checkoutShoppingCartRequest = $mindBodyService->calculateShoppingCart(
            $request->getSession()->get('mindbody_client_ID'),
            $request->getSession()->get('mindbody_service'),
            $discountCode
        );
        $grandTotal = $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal'];
        $discountAmount = $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['DiscountTotal'];
        $request->getSession()->set('grandTotal', $grandTotal);

        if (is_null($discountCode)) {
            $request->getSession()->remove('discountCode');
            $request->getSession()->remove('discountAmount');
        } else {
            $request->getSession()->set('discountCode', $discountCode);
            $request->getSession()->set('discountAmount', $discountAmount);
        }
        return new JsonResponse([
            'response' => $checkoutShoppingCartRequest,
            'discountAmount' => number_format($discountAmount, 0, '', '.'),
            'subTotal' => number_format($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['SubTotal'], 0, '', '.'),
            'grandTotal' => number_format($grandTotal, 0, '', '.'),
            'discountCode' => $discountCode
        ]);
    }

    /**
     * @Route("/calculate-limited-discount", name="calculate_limited_discount")
     * @param Request $request
     * @return JsonResponse
     */
    public function applyLimitedDiscountAction(Request $request)
    {
        if ($request->getSession()->has('limitedDiscountEnabled')) {
            $manager = $this->getDoctrine()->getManager();
            $customerDiscount = $manager->getRepository(CustomerDiscount::class)->find($request->getSession()->get('customerDiscountId'));
            $product = $manager->getRepository(Product::class)->findOneBy([
                'merchantId' => $request->request->get('productMerchantId')
            ]);

            $discountAmount = $product->getPrice() * ($customerDiscount->getDiscount()->getDiscountPercentage() / 100);
            $grandTotal = $product->getPrice() - $discountAmount;

            $request->getSession()->set('discountAmount', $discountAmount);
            $request->getSession()->set('mindbody_service', $request->request->get('productMerchantId'));

            return new JsonResponse([
                'grandTotal' => number_format($grandTotal, 0, '', '.'),
                'discountAmount' => number_format($discountAmount, 0, '', '.'),
                'productName' => $product->getName()
            ]);
        } else {
            return new JsonResponse();
        }
    }
}
