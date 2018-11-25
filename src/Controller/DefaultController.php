<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Exception\NotValidLoginException;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\LoginType;
use MiguelAlcaino\MindbodyPaymentsBundle\Model\MindbodySession;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 *
 * @package MiguelAlcaino\MindbodyPaymentsBundle\Controller
 * @Route("mindbody")
 */
class DefaultController extends AbstractController
{
    /**
     * @param Request               $request
     * @param MindbodyService       $mindBodyService
     * @param ParameterBagInterface $parameterBag
     *
     * @return Response
     * @throws \Exception
     * @Route("/login", name="mindbody_customer_login", methods={"POST", "GET"})
     */
    public function loginCustomerAction(
        Request $request,
        MindBodyService $mindBodyService,
        ParameterBagInterface $parameterBag
    ) {
        $request->getSession()->set(MindbodySession::MINDBODY_SELECTED_SERVICE_ID_VAR_NAME, $request->query->get('service'));

        $form = $this->createForm(
            LoginType::class,
            null,
            [
                'method' => 'POST',
            ]
        );

        $arrayToReturn = [];

        $form->handleRequest($request);

        $arrayToReturn['form'] = $form->createView();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $validateLogin = $mindBodyService->validateLogin(
                    $form->get('email')->getData(),
                    $form->get('password')->getData()
                );

                $clients = $mindBodyService->getClients(
                    [
                        sprintf($validateLogin['ValidateLoginResult']['Client']['ID']),
                    ]
                );

                $locations = $mindBodyService->getRealLocations();

                $manager = $this->getDoctrine()->getManager();

                $systemCustomer = $manager->getRepository(Customer::class)->findOneBy(
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

                $manager->persist($systemCustomer);

                $manager->flush();

                $request->getSession()->set(MindbodySession::MINDBODY_CLIENT_GUID_VAR_NAME, $validateLogin['ValidateLoginResult']['GUID']);
                $request->getSession()->set(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME, $systemCustomer->getMerchantId());
                $request->getSession()->set(MindbodySession::MINDBODY_CLIENT_EMAIL_VAR_NAME, $systemCustomer->getEmail());

                if (!array_key_exists('HomeLocation', $clients['GetClientsResult']['Clients']['Client'])) {
                    $request->getSession()->set(MindbodySession::MINDBODY_REAL_LOCATIONS_VAR_NAME, $locations);
                } else {
                    $request->getSession()->set(
                        MindbodySession::MINDBODY_USER_PROFERRED_LOCATION_ID_VAR_NAME,
                        $clients['GetClientsResult']['Clients']['Client']['HomeLocation']['ID']
                    );
                }

                if ($request->getSession()->has('referral')) {
                    $referral = $request->getSession()->get('referral');

                    return $this->redirect($referral);
                } else {
                    if ($parameterBag->has('login_success_route')) {
                        return $this->redirectToRoute($parameterBag->get('login_success_route'));
                    } else {
                        throw new \Exception('A login_success_route param has to be defined');
                    }
                }
            } catch (NotValidLoginException $exception) {
                $arrayToReturn['errorMessage'] = 'Incorrect email or password. Please try again.';

                return $this->renderLoginForm($arrayToReturn, $parameterBag->get('login_template'));
            }
        } else {
            return $this->renderLoginForm($arrayToReturn, $parameterBag->get('login_template'));
        }
    }

    /**
     * @param Request         $request
     * @param MindBodyService $mindBodyService
     *
     * @return Response
     * @Route("/apply-discount", name="mindbody_apply_discount", methods={"POST"})
     *
     * @return JsonResponse
     * @throws \MiguelAlcaino\MindbodyPaymentsBundle\Exception\InvalidItemInSHoppingCartException
     */
    public function applyDiscountCodeAction(Request $request, MindBodyService $mindBodyService)
    {
        $discountCode = $request->request->get(MindbodySession::MINDBODY_DISCOUNT_CODE_USED_VAR_NAME);
        if (empty($discountCode)) {
            throw new \Exception('Not valid discount code');
        }
        $checkoutShoppingCartRequest = $mindBodyService->calculateShoppingCart(
            $request->getSession()->get(MindbodySession::MINDBODY_CLIENT_ID_VAR_NAME),
            $request->getSession()->get(MindbodySession::MINDBODY_SELECTED_SERVICE_ID_VAR_NAME),
            $discountCode
        );
        $grandTotal                  = $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal'];
        $discountAmount              = $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['DiscountTotal'];
        $request->getSession()->set(MindbodySession::MINDBODY_GRAND_TOTAL_VAR_NAME, $grandTotal);

        if (is_null($discountCode)) {
            $request->getSession()->remove(MindbodySession::MINDBODY_DISCOUNT_CODE_USED_VAR_NAME);
            $request->getSession()->remove(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME);
        } else {
            $request->getSession()->set(MindbodySession::MINDBODY_DISCOUNT_CODE_USED_VAR_NAME, $discountCode);
            $request->getSession()->set(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME, $discountAmount);
        }

        return new JsonResponse(
            [
                'response'       => $checkoutShoppingCartRequest,
                'discountAmount' => number_format($discountAmount, 0, '', '.'),
                'subTotal'       => number_format($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['SubTotal'], 0, '', '.'),
                'grandTotal'     => number_format($grandTotal, 0, '', '.'),
                'discountCode'   => $discountCode,
            ]
        );
    }

    /**
     * Returns a rendered view of a Login form
     *
     * @param array       $viewParams
     * @param string|null $loginTemplatePath
     *
     * @return Response
     */
    private function renderLoginForm(array $viewParams, string $loginTemplatePath = null)
    {
        if (!is_null($loginTemplatePath)) {
            return $this->render($loginTemplatePath, $viewParams);
        } else {
            return $this->render('@MiguelAlcainoMindbodyPayments/Default/login.html.twig', $viewParams);
        }
    }
}
