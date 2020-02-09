<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use MiguelAlcaino\MindbodyPaymentsBundle\Form\Widget\CheckoutForm;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodyRequestHandler\ClientServiceRequestHandler;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodyRequestHandler\SaleServiceRequestHandler;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\ClassServiceSOAPRequester;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService\AddClientToClassRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClassService\GetClassesRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\ClientService\GetClientServicesRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\ShoppingCart\CartItemRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\Request\SaleService\ShoppingCart\ItemRequest;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Session\FromSessionService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Session\UserSessionService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\ShoppingCart\ShoppingCartService;
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
     * @param FromSessionService $fromSessionService
     *
     * @return GetClassesRequest
     */
    private function createGetClassesRequestFromSession(FromSessionService $fromSessionService): GetClassesRequest
    {
        return (new GetClassesRequest())
            ->setClassIDs(
                [
                    $fromSessionService->getMindbodyClassId(),
                ]
            )
            ->setStartDateTime(\DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $fromSessionService->getMindbodyClassStartTime()))
            ->setEndDateTime(\DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $fromSessionService->getMindbodyClassEndTime()));
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
        $getClassesRequest        = $this->createGetClassesRequestFromSession($fromSessionService);
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

            $viewParams['schedulePage'] = $parameterBag->get('schedule_page_url');

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
            return $this->redirectToRoute('mindbody_customer_login',[
                'template' => 'widget'
            ]);
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
        $getClassesRequest = $getClassesRequest = $this->createGetClassesRequestFromSession($fromSessionService);
        $dbServices        = $shoppingCartService->getFilteredServicesByClassId($getClassesRequest);

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
            '@MiguelAlcainoMindbodyPayments/widget/choosePackage.html.twig',
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
        $getClassesRequest = $getClassesRequest = $this->createGetClassesRequestFromSession($fromSessionService);
        $dbServices        = $shoppingCartService->getFilteredServicesByClassId($getClassesRequest);

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
     * @Route("/successful-payment", name="widget_successful_payment")
     * @param FromSessionService    $fromSessionService
     * @param ParameterBagInterface $parameterBag
     *
     * @return Response
     */
    public function successfulPaymentAction(FromSessionService $fromSessionService, ParameterBagInterface $parameterBag)
    {
        $transactionRecord = $fromSessionService->getTransactionRecord();

        return $this->render(
            '@MiguelAlcainoMindbodyPayments/widget/successfulPayment.html.twig',
            [
                'transactionRecord' => $transactionRecord,
                'nextUrl'           => $parameterBag->get('schedule_page_url'),
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
}
