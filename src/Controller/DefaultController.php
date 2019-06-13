<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use MiguelAlcaino\MindbodyPaymentsBundle\Form\LoginType;
use MiguelAlcaino\MindbodyPaymentsBundle\Model\MindbodySession;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Session\FromSessionService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\TwigExtension\PriceFormatExtension;
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
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route("/login", name="mindbody_customer_login", methods={"POST", "GET"})
     */
    public function loginCustomerAction(
        Request $request
    ) {
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

        return $this->renderLoginForm($arrayToReturn);
    }

    /**
     * @param Request              $request
     * @param MindBodyService      $mindBodyService
     * @param PriceFormatExtension $priceFormatExtension
     *
     * @return Response
     * @throws \MiguelAlcaino\MindbodyPaymentsBundle\Exception\InvalidItemInShoppingCartException
     * @Route("/apply-discount", name="mindbody_apply_discount", methods={"POST"})
     *
     */
    public function applyDiscountCodeAction(Request $request, MindBodyService $mindBodyService, PriceFormatExtension $priceFormatExtension)
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

        $grandTotal     = $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['GrandTotal'];
        $discountAmount = $checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['DiscountTotal'];

        $request->getSession()->set(MindbodySession::MINDBODY_GRAND_TOTAL_VAR_NAME, $grandTotal);

        if ($discountCode === null) {
            $request->getSession()->remove(MindbodySession::MINDBODY_DISCOUNT_CODE_USED_VAR_NAME);
            $request->getSession()->remove(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME);
        } else {
            $request->getSession()->set(MindbodySession::MINDBODY_DISCOUNT_CODE_USED_VAR_NAME, $discountCode);
            $request->getSession()->set(MindbodySession::MINDBODY_DISCOUNT_AMOUNT_VAR_NAME, $discountAmount);
        }

        return new JsonResponse(
            [
                'response'       => $checkoutShoppingCartRequest,
                'discountAmount' => $priceFormatExtension->formatPrice($discountAmount),
                'subTotal'       => $priceFormatExtension->formatPrice($checkoutShoppingCartRequest['CheckoutShoppingCartResult']['ShoppingCart']['SubTotal']),
                'grandTotal'     => $priceFormatExtension->formatPrice($grandTotal),
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
    private function renderLoginForm(array $viewParams, string $loginTemplatePath = null): ?Response
    {
        if ($loginTemplatePath !== null) {
            return $this->render($loginTemplatePath, $viewParams);
        } else {
            return $this->render('@MiguelAlcainoMindbodyPayments/Default/login.html.twig', $viewParams);
        }
    }

    /**
     * @Route("/logout", name="mindbody_customer_logout")
     * @param FromSessionService $fromSessionService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function mindbodyCustomerLogout(FromSessionService $fromSessionService, ParameterBagInterface $parameterBag)
    {
        $fromSessionService->destroyMindbodySession();

        return $this->redirect($parameterBag->get('schedule_page'));
    }
}
