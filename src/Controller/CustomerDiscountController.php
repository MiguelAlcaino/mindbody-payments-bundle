<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomerDiscount;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\CustomerDiscountType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomerDiscountController
 * @package MindBodyPaymentsBundle\Controller
 * @Route("/admin/customer-discount")
 */
class CustomerDiscountController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{id}/edit", name="admin_customer_discount_edit")
     */
    public function editAction(Request $request, CustomerDiscount $customerDiscount)
    {
        $form = $this->createForm(CustomerDiscountType::class, $customerDiscount, [
            'action' => $this->generateUrl('admin_customer_discount_update', [
                'id' => $customerDiscount->getId()
            ]),
            'method' => 'POST'
        ]);
        return $this->render('@MiguelAlcainoMindbodyPayments/customerdiscount/edit.html.twig', [
            'form' => $form->createView(),
            'customerDiscount' => $customerDiscount
        ]);
    }

    /**
     * @param Request $request
     * @param CustomerDiscount $customerDiscount
     * @Route("/{id}/update", name="admin_customer_discount_update", methods={"POST"})
     */
    public function updateAction(Request $request, CustomerDiscount $customerDiscount)
    {
        $form = $this->createForm(CustomerDiscountType::class, $customerDiscount, [
            'action' => $this->generateUrl('admin_customer_discount_update', [
                'id' => $customerDiscount->getId()
            ]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $customerDiscount = $form->getData();
            $manager->persist($customerDiscount);
            $manager->flush();

            $this->addFlash('notice', 'El descuento ha sido actualizado para este usuario');
            return $this->redirectToRoute('admin_customer_discount_edit', [
                'id' => $customerDiscount->getId()
            ]);
        } else {
            return $this->render('@MiguelAlcainoMindbodyPayments/customerdiscount/edit.html.twig', [
                'form' => $form->createView(),
                'customerDiscount' => $customerDiscount
            ]);
        }
    }
}
