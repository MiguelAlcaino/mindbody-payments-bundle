<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use MiguelAlcaino\MindbodyPaymentsBundle\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/adm/login", name="admin_login")
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     */
    public function loginAdmin(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@MiguelAlcainoMindbodyPayments/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/login", name="mindbody_customer_login", methods={"POST", "GET"})
     */
    public function loginMindbodyCustomer(Request $request)
    {
        $form = $this->createForm(
            LoginType::class,
            null,
            [
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        return $this->render('@MiguelAlcainoMindbodyPayments/Default/login.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param ParameterBagInterface $parameterBag
     *
     * @return RedirectResponse
     * @Route("/logout-target", name="mindbody_logout_target", methods={"GET"})
     */
    public function mindbodyTargetLogout(ParameterBagInterface $parameterBag){
        return new RedirectResponse($parameterBag->get('schedule_page_url'));
    }
}
