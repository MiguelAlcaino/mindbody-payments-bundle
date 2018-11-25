<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\ProductDiscount;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\DiscountType;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Discount controller.
 *
 * @Route("admin/discount")
 */
class DiscountController extends Controller
{
    /**
     * Lists all discount entities.
     *
     * @Route("/", name="discount_index", methods={"GET"})
     */
    public function indexAction(EntityManagerInterface $em)
    {
        $discounts = $em->getRepository(Discount::class)->findAll();

        return $this->render(
            'index',
            [
                'discounts' => $discounts,
            ]
        );
    }

    /**
     * Creates a new discount entity.
     *
     * @Route("/new", name="discount_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $discount = new Discount();
        $form     = $this->createForm('MindBodyPaymentsBundle\Form\DiscountType', $discount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($discount);
            $em->flush();

            return $this->redirectToRoute('discount_show', ['id' => $discount->getId()]);
        }

        return $this->render(
            'discount/new.html.twig',
            [
                'discount' => $discount,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * Finds and displays a discount entity.
     *
     * @Route("/{id}", name="discount_show", methods={"GET"})
     */
    public function showAction(Discount $discount)
    {
        $deleteForm = $this->createDeleteForm($discount);

        return $this->render(
            'discount/show.html.twig',
            [
                'discount'    => $discount,
                'delete_form' => $deleteForm->createView(),
            ]
        );
    }

    /**
     * Displays a form to edit an existing discount entity.
     *
     * @Route("/{id}/edit", name="discount_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Discount $discount)
    {
        $deleteForm = $this->createDeleteForm($discount);
        $editForm   = $this->createForm('MindBodyPaymentsBundle\Form\DiscountType', $discount);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('discount_edit', ['id' => $discount->getId()]);
        }

        return $this->render(
            'discount/edit.html.twig',
            [
                'discount'    => $discount,
                'edit_form'   => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/save/{productId}", name="admin_discount_save", methods={"POST"})
     */
    public function saveDiscountAction(Request $request, int $productId)
    {
        $manager = $this->getDoctrine()->getManager();

        if ($request->query->has('discountId')) {
            $discount = $manager->getRepository(Discount::class)->find($request->query->get('discountId'));
        } else {
            $discount = new Discount();
            $discount->setType('product_discount');
        }

        $form = $this->createForm(DiscountType::class, $discount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formProducts   = $form->get('products')->getData();
            $formProductIds = [];

            /** @var Discount $discount */
            $discount = $form->getData();

            /** @var Product $product */
            foreach ($formProducts as $product) {
                $productDiscount = $manager->getRepository(ProductDiscount::class)->findOneBy(
                    [
                        'discount' => $discount,
                        'product'  => $product,
                    ]
                );

                if (is_null($productDiscount)) {
                    $productDiscount = (new ProductDiscount())
                        ->setProduct($product)
                        ->setDiscount($discount);
                }

                if ($product->getId() == $productId) {
                    $productDiscount->setIsMainProduct(true);
                }

                $productDiscount->setActive(true);

                $formProductIds[] = $product->getId();

                $manager->persist($productDiscount);
            }

            if (!is_null($discount->getId())) {
                $newRemovedProductDiscounts = $manager
                    ->getRepository(ProductDiscount::class)
                    ->getOtherProductsOfDiscount($discount, $formProductIds);

                foreach ($newRemovedProductDiscounts as $newRemovedProductDiscount) {
                    $newRemovedProductDiscount->setActive(false);
                    $manager->persist($newRemovedProductDiscount);
                }
            }

            $manager->persist($discount);

            $manager->flush();

            return $this->redirectToRoute(
                'admin_product_edit',
                [
                    'id' => $productId,
                ]
            );
        }

        return $this->render(
            'discount/new.html.twig',
            [
                'discount' => $discount,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @param Request  $request
     * @param Customer $customer
     * @Route("/new-for-customer/{id}", name="admin_discount_new_discount_for_customer")
     */
    public function newDiscountForCustomerAction(Request $request, Customer $customer)
    {
        $discount = new Discount();
        $discount->setType('customer_discount');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/email/preview", name="admin_discount_preview_discount_email", methods={"POST"})
     */
    public function emailPreviewAction(Request $request, EmailService $emailService)
    {
        $manager            = $this->getDoctrine()->getManager();
        $emailBody          = $request->request->get('emailBody');
        $productIds         = $request->request->get('productIds');
        $beforeAfter        = $request->request->get('beforeAfter');
        $discountPercentage = $request->request->get('discountPercentage');
        $days               = $request->request->get('days');

        $products = $manager->getRepository(Product::class)->findBy(
            [
                'id' => $productIds,
            ]
        );

        $customer     = $manager->getRepository(Customer::class)->getLastCustomer();
        $newEmailBody = $emailService->decodeEmailBody(
            $emailBody,
            [
                'products'           => $products,
                'user'               => $customer,
                'beforeAfter'        => $beforeAfter,
                'discountPercentage' => $discountPercentage,
                'days'               => $days,
                'discountLink'       => 'https://pagos.cyglocolombia.com/discount/notvaliddiscount',
                'discountUntil'      => (new \DateTime()),
            ]
        );

        return new JsonResponse(
            [
                'view' => $newEmailBody,
            ]
        );
    }

    /**
     * @Route("/disable", name="admin_discount_disable_discount", methods={"POST"})
     */
    public function disableDiscountAction(Request $request)
    {
        $id       = $request->request->get('discountId');
        $enabled  = $request->request->get('enabled') === 'true';
        $manager  = $this->getDoctrine()->getManager();
        $discount = $manager->getRepository(Discount::class)->find($id);

        $discount->setEnabled($enabled);

        $manager->persist($discount);
        $manager->flush();

        return new JsonResponse(
            [
                'id'      => $id,
                'enabled' => $discount->getEnabled(),
            ]
        );
    }

}
