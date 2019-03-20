<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionItem;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Exception\NoneServiceFoundException;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\Widget\CheckoutForm;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyRequestHandler\ClientServiceRequestHandler;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyRequestHandler\SaleServiceRequestHandler;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\ClassServiceSOAPRequester;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\ClassService\AddClientToClassRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\ClassService\GetClassesRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\ClientService\GetClientServicesRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\SaleService\ShoppingCart\CartItemRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest\Request\SaleService\ShoppingCart\ItemRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Session\FromSessionService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Session\UserSessionService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\ShoppingCart\ShoppingCartService;
use MiguelAlcaino\PaymentGateway\Exception\Charge\CreditCardChargeException;
use MiguelAlcaino\PaymentGateway\Interfaces\PaymentGatewayRouterInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WidgetController
 *
 * @package MiguelAlcaino\MindbodyPaymentsBundle\Controller
 * @Route("/widget")
 */
class WidgetController extends AbstractController
{
    /**
     * Test purposes
     *
     * @return Response
     * @Route("/test-enrollment", name="widget_test_enrollment")
     */
    public function enrollmentTestAction()
    {
        return $this->render('@MiguelAlcainoMindbodyPayments/widget/enrollment.html.twig');
    }

    /**
     * Test Purposes
     *
     * @param Request $request
     *
     * @return Response
     * @Route("/test-index", name="widget_test_index")
     */
    public function testIndexAction(Request $request)
    {
        return $this->render(
            '@MiguelAlcainoMindbodyPayments/widget/testIndex.html.twig',
            [
                'host' => $request->getSchemeAndHttpHost(),
            ]
        );
    }

    /**
     * Test Purposes
     * @Route("/set-values", name="widget_set_values")
     */
    public function setValuesAction(Request $request)
    {
        if ($this->get('kernel')->getEnvironment() !== 'dev') {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_BAD_GATEWAY);

            return $response;
        }
        $request->getSession()->set('mindbody_client_ID', $request->query->get('id'));
        $clients = $this->get('mind_body_service')->getClients([$request->query->get('id')]);

        $request->getSession()->set('mindbody_client_email', $clients['GetClientsResult']['Clients']['Client']['Email']);
        $request->getSession()->set('mindbody_client_guid', random_int(0, 999999));

        return $this->redirect($this->getParameter('schedule_page'));
    }

    /**
     * @param Request            $request
     * @param FromSessionService $fromSessionService
     *
     * @return array|Response
     * @Route("/book-summary", name="widget_book_summary")
     */
    public function bookSummaryAction(Request $request, FromSessionService $fromSessionService)
    {
        $teacherName    = $request->query->get('teacherName');
        $className      = $request->query->get('className');
        $classStartTime = $request->query->get('classStartTime');
        $classEndTime   = $request->query->get('classEndTime');
        $classId        = $request->query->get('classId');

        $fromSessionService->removeMindbodyClassType();
        $fromSessionService->removeDiscountAmount();
        $fromSessionService->removeTransactionRecord();
        $fromSessionService->removePaymentResponse();
        $fromSessionService->removeMindbodyClientCurrentServiceId();
        $fromSessionService->removeAmount();
        $fromSessionService->removeMindbodyClassType();

        if ($fromSessionService->hasMindbodyClassType()) {
            $classType = $request->query->get('classType');
            $fromSessionService->setMindbodyClassType($classType);
        }

        $fromSessionService->setMindbodyClassId($classId);
        $fromSessionService->setMindbodyClassTeacherName($teacherName);
        $fromSessionService->setMindbodyClassName($className);
        $fromSessionService->setMindbodyClassStartTime($classStartTime);
        $fromSessionService->setMindbodyClassEndTime($classEndTime);

        return $this->render(
            '@MiguelAlcainoMindbodyPayments/widget/bookSummary.html.twig',
            [
            'teacherName'    => $teacherName,
            'className'      => $className,
            'classStartTime' => $classStartTime,
            'classEndTime'   => $classEndTime,
            ]
        );
    }

    /**
     * @param FromSessionService    $fromSessionService
     * @param MindbodyService       $mindbodyService
     * @param LoggerInterface       $logger
     * @param ParameterBagInterface $parameterBag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route("/signup-for-enrollment", name="widget_sign_up_for_enrollment")
     */
    public function signUpEnrollmentAction(
        FromSessionService $fromSessionService,
        MindbodyService $mindbodyService,
        LoggerInterface $logger,
        ParameterBagInterface $parameterBag
    ) {
        if ($this->isUserReadyToBookClass()) {
            $signUpForEnrollmentResult = $mindbodyService->addClientsToEnrollments(
                $fromSessionService->getMindbodyClientID(),
                $fromSessionService->getMindbodyClassId()
            );

            $viewParams           = [];
            $viewParams['result'] = $signUpForEnrollmentResult;

            if (
                $signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['ErrorCode'] !== 200
                || $signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['Status'] === 'FailedAction'
            ) {
                if (
                    isset($signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['Enrollments']['ClassSchedule']['Clients']['Client']['ErrorCode'])
                    && $signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['Enrollments']['ClassSchedule']['Clients']['Client']['ErrorCode']
                    === 603
                ) {
                    $viewParams['errorMessage'] = 'You are registered in this class already';
                } elseif (
                    isset($signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['Enrollments']['ClassSchedule']['Clients']['Client']['ErrorCode'])
                    && $signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['Enrollments']['ClassSchedule']['Clients']['Client']['ErrorCode']
                    === 602
                ) {
                    $viewParams['errorMessage'] = 'You\'re out of the scheduling window';
                } elseif (
                    isset($signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['Enrollments']['ClassSchedule']['Clients']['Client']['ErrorCode'])
                    && $signUpForEnrollmentResult['AddClientsToEnrollmentsResult']['Enrollments']['ClassSchedule']['Clients']['Client']['ErrorCode']
                    === 601
                ) {
                    return $this->redirectToRoute('widget_checkout');
                } else {
                    $viewParams['errorMessage'] = 'There\'s been a mistake on your reservation. Please try again.';
                    $logger->error('Error trying to signup enrollment', $signUpForEnrollmentResult);
                }
            }
            $viewParams['schedulePage'] = $parameterBag->get('enrollment_page');

            return $this->render('@MiguelAlcainoMindbodyPayments/widget/signUpForClass.html.twig', $viewParams);
        } else {
            return $this->redirectToRoute('mindbody_customer_login');
        }
    }

    /**
     * @param FromSessionService          $fromSessionService
     * @param ClassServiceSOAPRequester   $classServiceSOAPRequester
     * @param ClientServiceRequestHandler $clientServiceRequestHandler
     * @param TranslatorInterface         $translator
     * @param LoggerInterface             $logger
     * @param ParameterBagInterface       $parameterBag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @Route("/signup-for-class", name="widget_sign_up_for_class")
     */
    public function signUpClassAction(
        FromSessionService $fromSessionService,
        ClassServiceSOAPRequester $classServiceSOAPRequester,
        ClientServiceRequestHandler $clientServiceRequestHandler,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        ParameterBagInterface $parameterBag
    )
    {
        $getClassesRequest = (new GetClassesRequest())
            ->setClassIDs(
                [
                    $fromSessionService->getMindbodyClassId(),
                ]
            );

        $getClassesResponse       = $classServiceSOAPRequester->getClasses($getClassesRequest);
        $programId                = $getClassesResponse['GetClassesResult']['Classes']['Class']['ClassDescription']['Program']['ID'];
        $getClientServicesRequest = new GetClientServicesRequest(
            $fromSessionService->getMindbodyClientID(),
            [$programId]
        );

        $getClientServicesRequest->setClassID($fromSessionService->getMindbodyClassId());

        $canUserBookThisClass = $clientServiceRequestHandler->canClientBookClass($getClientServicesRequest);

        if ($canUserBookThisClass) {
            $addClientToClassRequest = new AddClientToClassRequest(
                $fromSessionService->getMindbodyClientID(),
                $fromSessionService->getMindbodyClassId()
            );

            $signUpForClassResult = $classServiceSOAPRequester->addClientToClass($addClientToClassRequest);
            $viewParams           = [];
            $viewParams['result'] = $signUpForClassResult;

            if ((int)$signUpForClassResult['AddClientToClassResult']['ErrorCode'] !== 200) {
                if (
                    isset($signUpForClassResult['AddClientToClassResult']['Classes']['Class']['Clients']['Client']['ErrorCode'])
                    && $signUpForClassResult['AddClientToClassResult']['Classes']['Class']['Clients']['Client']['ErrorCode'] == 603
                ) {
                    $viewParams['errorMessage'] = $translator->trans('notice.widget.client_registered_already_in_class');
                } elseif (
                    isset($signUpForClassResult['AddClientToClassResult']['Classes']['Class']['Clients']['Client']['ErrorCode'])
                    && $signUpForClassResult['AddClientToClassResult']['Classes']['Class']['Clients']['Client']['ErrorCode'] == 602
                ) {
                    $viewParams['errorMessage'] = $translator->trans('notice.widget.client_outside_schedule_window');
                } elseif (
                    isset($signUpForClassResult['AddClientToClassResult']['Classes']['Class']['Clients']['Client']['ErrorCode'])
                    && $signUpForClassResult['AddClientToClassResult']['Classes']['Class']['Clients']['Client']['ErrorCode'] == 601
                ) {
                    //TODO: SOMETIMES HERE A LOOP IS HAPPENING. This route should be dynamic with a default value
                    return $this->redirectToRoute('widget_checkout');
                } else {
                    $viewParams['errorMessage'] = $translator->trans('error.widget.error_add_user_to_class');
                    $logger->error('Error trying to add user to class', $signUpForClassResult);
                }
            }

            $viewParams['schedulePage'] = $parameterBag->get('schedule_page');

            return $this->render('@MiguelAlcainoMindbodyPayments/widget/signUpForClass.html.twig', $viewParams);
        } else {
            return $this->redirectToRoute('widget_checkout');
        }
    }

    /**
     *
     * @param FromSessionService $fromSessionService
     * @param UserSessionService $userSessionService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/before-login", name="widget_before_login")
     */
    public function beforeLoginAction(
        FromSessionService $fromSessionService,
        UserSessionService $userSessionService
    ) {
        if ($userSessionService->isUserLoggedIn()) {
            if ($fromSessionService->getMindbodyClassType() === 'enrollment') {
                return $this->redirectToRoute('widget_sign_up_for_enrollment');
            } else {
                return $this->redirectToRoute('widget_sign_up_for_class');
            }
        } else {
            return $this->redirectToRoute('mindbody_customer_login');
        }
    }

    /**
     * @param FromSessionService  $fromSessionService
     * @param ShoppingCartService $shoppingCartService
     *
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @Route("/checkout", name="widget_checkout")
     */
    public function choosePackageAction(
        FromSessionService $fromSessionService,
        ShoppingCartService $shoppingCartService
    ) {
        $dbServices = $shoppingCartService->getFilteredServicesByClassId($fromSessionService->getMindbodyClassId());

        $form = $this->createForm(
            CheckoutForm::class,
            null,
            [
                'services' => $dbServices,
                'method'   => 'POST',
                'action'   => $this->generateUrl('widget_calculate_shopping_cart'),
            ]
        );

        return $this->render(
            '@MiguelAlcainoMindbodyPayments/widget/checkout.html.twig',
            [
                'services' => $dbServices,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @param Request                       $request
     * @param FromSessionService            $fromSessionService
     * @param ShoppingCartService           $shoppingCartService
     * @param SaleServiceRequestHandler     $saleServiceRequestHandler
     * @param TranslatorInterface           $translator
     * @param PaymentGatewayRouterInterface $paymentGatewayRouter
     *
     * @return array|Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @Route("/calculate-shopping-cart", name="widget_calculate_shopping_cart", methods={"POST"})
     */
    public function calculateShoppingCartAction(
        Request $request,
        FromSessionService $fromSessionService,
        ShoppingCartService $shoppingCartService,
        SaleServiceRequestHandler $saleServiceRequestHandler,
        TranslatorInterface $translator,
        PaymentGatewayRouterInterface $paymentGatewayRouter
    ) {
        $dbServices = $shoppingCartService->getFilteredServicesByClassId($fromSessionService->getMindbodyClassId());

        $form = $this->createForm(
            CheckoutForm::class,
            null,
            [
                'services' => $dbServices,
                'method'   => 'POST',
                'action'   => $this->generateUrl('widget_calculate_shopping_cart'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //service here is the Mindbody price selected
            $serviceId = $form->get('service')->getData();

            $cartItem = new CartItemRequest(
                new ItemRequest(
                $serviceId,
                    'Service' //TODO: Change this adding $type to Product
                ), 1
            );

            $cartItems = [$cartItem];

            $responseCalculateShoppingCart = $saleServiceRequestHandler->calculateShoppingCart(
                $fromSessionService->getMindbodyClientID(),
                $cartItems
            );

            if ((int)$responseCalculateShoppingCart['CheckoutShoppingCartResult']['ErrorCode'] === 200) {
                $fromSessionService->setSelectedMindbodyServiceId($serviceId);
                $fromSessionService->setSelectedMindbodyServiceName(
                    $responseCalculateShoppingCart['CheckoutShoppingCartResult']['ShoppingCart']['CartItems']['CartItem']['Item']['Name']
                );
                $fromSessionService->setAmount(
                    $responseCalculateShoppingCart['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal']
                );
            } else {
                $this->addFlash('error', $translator->trans('widget.error.chopping_cart_calculation_error'));
                return $this->redirectToRoute('widget_checkout');
            }

            return $this->redirectToRoute($paymentGatewayRouter->getPaymentFormRoute());
        } else {
            return $this->redirectToRoute('widget_checkout');
        }
    }

    /**
     * @return array|Response
     * @Route("/credit-card-form", name="widget_credit_card_form")
     * @Template("@MindBodyPayments/Widget/makePayment.html.twig")
     */
    public function creditCardFormAction(Request $request)
    {
        if (!$request->getSession()->has('mindbody_client_ID')) {
            try {
                $services = $this->getServices($request);
            } catch (NoneServiceFoundException $exception) {
                return $exception->getRoute();
            }

            $chosenService = null;
            if ($request->query->has('serviceId')) {
                foreach ($services as $service) {
                    if ($request->query->get('serviceId') == $service['id']) {
                        $chosenService = $service;
                    }
                }
                if (!is_null($chosenService)) {
                    $request->getSession()->remove('className');
                    $request->getSession()->remove('teacherName');
                    $request->getSession()->set('serviceId', $chosenService['id']);
                    $request->getSession()->set(
                        'referral',
                        $this->generateUrl(
                            'widget_credit_card_form',
                            [
                                'serviceId' => $chosenService['id'],
                            ]
                        )
                    );
                }
            }

            return $this->redirectToRoute('mindbody_customer_login');
        } else {
            if ($request->query->has('serviceId')) {
                try {
                    $services = $this->getServices($request);
                } catch (NoneServiceFoundException $exception) {
                    return $exception->getRoute();
                }

                $chosenService = null;
                foreach ($services as $service) {
                    if ($request->query->get('serviceId') == $service['id']) {
                        $chosenService = $service;
                    }
                }
                if (!is_null($chosenService)) {
                    $request->getSession()->set('serviceId', $chosenService['id']);
                    $responseCalculateShoppingCart = $this->get('mind_body_service')->calculateShoppingCart(
                        $request->getSession()->get('mindbody_client_ID'),
                        $chosenService['id'],
                        null
                    );

                    $request->getSession()->set(
                        'serviceName',
                        $responseCalculateShoppingCart['CheckoutShoppingCartResult']['ShoppingCart']['CartItems']['CartItem']['Item']['Name']
                    );
                    $request->getSession()->set(
                        'grandTotal',
                        $responseCalculateShoppingCart['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal']
                    );
                }
            }
        }

        if ($request->getSession()->has('errorMessage')) {
            $viewParams['errorMessage'] = $request->getSession()->get('errorMessage');
            $request->getSession()->remove('errorMessage');
        }

        return $this->redirectToRoute('miguelalcaino_migs_reddirect_to_parent_window');
    }

    /**
     * @param Request $request
     * @Route("/process-payment", name="widget_process_payment", methods={"POST"})
     */
    public function processPaymentAction(Request $request, FromSessionService $fromSessionService)
    {
        $creditCardForm = $this->createForm(
            CreditCardJustTextType::class,
            null,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('widget_process_payment'),
            ]
        );

        $creditCardForm->handleRequest($request);

        if ($creditCardForm->isSubmitted() && $creditCardForm->isValid()) {
            $manager           = $this->getDoctrine()->getManager();
            $creditCardToken   = $creditCardForm->get('jsCreditCardToken')->getData();
            $transactionRecord = new TransactionRecord();
            $systemCustomer    = $manager->getRepository(Customer::class)->findOneBy(
                [
                    'email' => $request->getSession()->get('mindbody_client_email'),
                ]
            );

            $taxAmount = ($this->getParameter('tax_percentage') / 100) * $request->getSession()->get('grandTotal');

            $transactionRecord->setCustomer($systemCustomer)
                ->setCreditCardHolderName($creditCardForm->get('cardHolderName')->getData())
                ->setDocumentNumber($creditCardForm->get('documentNumber')->getData())
                ->setCreditCardLastFourDigits(substr($creditCardForm->get('cardNumber')->getData(), -4))
                ->setTaxAmount($taxAmount)
                ->setInstallments($creditCardForm->get('installments')->getData())
                ->setPaymentGatewayFee(0)
                ->setUserCountry($systemCustomer->getUserCountry());
            $transactionItem = new TransactionItem();
            $transactionItem
                ->setName($request->getSession()->get('serviceName'))
                ->setPrice($request->getSession()->get('grandTotal'));
            try {
                $charge = $this->get('payment_gateway_service')->getPaymentGatewayService()->charge(
                    $transactionRecord,
                    $transactionItem,
                    $creditCardToken
                );
            } catch (CreditCardChargeException $exception) {
                $request->getSession()->set('errorMessage', 'There was an error processing your card. Please try again.');

                return $this->redirectToRoute('widget_credit_card_form');
            }

            /** @var TransactionRecord $transactionRecord */
            $transactionRecord = $charge['transactionRecord'];
            $result            = $charge['result'];

            $customPaymentMethods = $this->get('mind_body_service')->getFormattedCustomPaymentMethods();

            if ($request->getSession()->has('discountAmount')) {
                $discountAmount = $request->getSession()->get('discountAmount');
                $transactionRecord->setDiscountAmount($discountAmount);
            } else {
                $discountAmount = 0;
            }

            $transactionRecord = $this->get('mind_body_service')->makePurchase(
                $transactionRecord,
                $request->getSession()->get('mindbody_client_ID'),
                $request->getSession()->get('serviceId'),
                $customPaymentMethods['iugu'],
                $request->getSession()->get('grandTotal'),
                null,
                $discountAmount
            );

            $manager->persist($transactionRecord);

            $manager->flush();

            $fromSessionService->setPaymentResponse($result);
            $fromSessionService->setTransactionRecord($transactionRecord);

            return $this->redirectToRoute('widget_successful_payment');
        } else {
            return $this->render(
                '@MindBodyPayments/Widget/makePayment.html.twig',
                [
                    'paymentGatewayJsId' => $this->getParameter('iugu.js_id'),
                    'form'               => $creditCardForm->createView(),
                    'serviceName'        => $request->getSession()->get('serviceName'),
                    'grandTotal'         => $request->getSession()->get('grandTotal'),
                ]
            );
        }
    }

    /**
     * @Route("/successful-payment", name="widget_successful_payment")
     */
    public function successfulPaymentAction(Request $request)
    {
        $transactionRecord = $request->getSession()->get('transactionRecord');
        if ($request->getSession()->has('className')) {
            $routeParams = [
                'className'      => $request->getSession()->get('className'),
                'teacherName'    => $request->getSession()->get('teacherName'),
                'classStartTime' => $request->getSession()->get('classStartTime'),
                'classEndTime'   => $request->getSession()->get('classEndTime'),
                'classId'        => $request->getSession()->get('mindbody_class_ID'),
            ];

            if ($request->getSession()->has('classType') && $request->getSession()->get('classType') === 'enrollment') {
                $routeParams['classType'] = $request->getSession()->get('classType');
            }

            $nextUrl = $this->generateUrl('widget_book_summary', $routeParams);
        } else {
            $nextUrl = $this->getParameter('schedule_page');
        }

        return $this->render(
            '@MindBodyPayments/Widget/successfulPayment.html.twig',
            [
                'transactionRecord' => $transactionRecord,
                'nextUrl'           => $nextUrl,
            ]
        );
    }

    /**
     * @param Request $request
     * @Route("/register-new-mindobdy-user", name="widget_register_new_mindbody_user")
     * @Template("@MindBodyPayments/Widget/registerNewUser.html.twig")
     */
    public function registerNewUserMindbodyAction(Request $request)
    {
        $requiredFields = $this->get('mind_body_service')->getRequiredClientFields();
        $referralTypes  = $this->get('mind_body_service')->getClientReferralTypes();
        $form           = $this->createForm(
            NewMindbodyUserType::class,
            null,
            [
                'additionalFields' => $requiredFields,
                'referralTypes'    => $referralTypes,
            ]
        );

        $form->handleRequest($request);
        $viewParams = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach ($data as $key => $datum) {
                switch ($key) {
                    case 'BirthDate':
                        break;
                }
                if ($key === 'BirthDate') {
                    $data[$key] = $datum->format('Y-m-d');
                }
            }

            $data['Username'] = $data['Email'];

            try {
                $searchedClient = $this->get('mind_body_service')->searchClientByEmail($form->get('Email')->getData());
                $newUser        = $this->get('mind_body_service')->addOrUpdateClients($data);

                $this->addFlash(
                    'notice',
                    'Your account has been successfully created. Please login now.'
                );

                return $this->redirectToRoute('mindbody_customer_login');
            } catch (EmailAlreadyExistsException $exception) {
                $viewParams['emailError'] = 'The email is already registered. Try with another one.';
            } catch (PasswordNotComplexException $exception) {
                $viewParams['passwordError'] = 'Password error. The password must contain at least 6 characters and uppercase letters.';
            }
        }

        $viewParams['form'] = $form->createView();

        return $viewParams;
    }

    private function getServices(Request $request)
    {
        if ($request->getSession()->has('classType') && $request->getSession()->get('classType') === 'enrollment') {
            $programIds = [
                [
                    'id' => $this->getParameter('enrollment_program_id'),
                ],
            ];
        } else {
            $programIds = [
                [
                    'id' => $this->getParameter('class_program_id'),
                ],
            ];
        }
        try {
            if ($request->getSession()->has('classType') && $request->getSession()->get('classType') === 'enrollment') {
                $services = $this->get('mind_body_service')->getFormattedServices(
                    false,
                    $programIds,
                    $request->getSession()->get('mindbody_class_ID')
                );
            } else {
                $services = $this->get('mind_body_service')->getFormattedServices(false, $programIds);
            }
        } catch (NoneServiceFoundException $exception) {
            $this->get('logger')->error('None service has been found');
            $routeParams = [
                'className'      => $request->getSession()->get('className'),
                'teacherName'    => $request->getSession()->get('teacherName'),
                'classStartTime' => $request->getSession()->get('classStartTime'),
                'classEndTime'   => $request->getSession()->get('classEndTime'),
                'classId'        => $request->getSession()->get('mindbody_class_ID'),
            ];

            if ($request->getSession()->has('classType') && $request->getSession()->get('classType') === 'enrollment') {
                $routeParams['classType'] = 'enrollment';
            }

            $exception->setRoute($this->redirectToRoute('widget_book_summary', $routeParams));
            throw $exception;
        }

        return $services;
    }

}
