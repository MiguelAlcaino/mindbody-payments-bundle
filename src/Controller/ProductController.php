<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\DiscountType;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\ProductType;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Product controller.
 *
 * @Route("product")
 */
class ProductController extends Controller
{
    /**
     * Lists all product entities.
     *
     * @Route("/", name="admin_product_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $products = $em->getRepository(Product::class)->findBy(
            [
                'isDeleted' => false,
            ]
        );

        return $this->render(
            '@MiguelAlcainoMindbodyPayments/product/index.html.twig',
            [
                'products' => $products,
            ]
        );
    }

    /**
     * Finds and displays a product entity.
     *
     * @Route("/{id}", name="admin_product_show", methods={"GET"})
     */
    public function showAction(Product $product)
    {
        return $this->render(
            '@MiguelAlcainoMindbodyPayments/product/show.html.twig',
            [
                'product' => $product,
            ]
        );
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", name="admin_product_edit", methods={"GET"})
     */
    public function editAction(Request $request, Product $product)
    {
        $manager = $this->getDoctrine()->getManager();

        $currentCustomers = $manager->getRepository(Customer::class)->getCurrentCustomersOfProduct($product);
        $editForm         = $this->createForm(ProductType::class, $product);
        $editForm->handleRequest($request);

        /** @var Discount $mainDiscount */
        $mainDiscount = $manager->getRepository(Product::class)->getMainDiscount($product);

        if (!is_null($mainDiscount)) {
            $discount        = $mainDiscount;
            $defaultProducts = $manager->getRepository(Product::class)->getActiveProductsOfDiscount($discount);
        } else {
            $discount        = null;
            $defaultProducts = (new ArrayCollection());
            $defaultProducts->add($product);
        }

        $discountForm = $this->createForm(
            DiscountType::class,
            $discount,
            [
                'defaultProducts' => $defaultProducts,
                'action'          => $this->generateUrl(
                    'admin_discount_save',
                    [
                        'productId'  => $product->getId(),
                        'discountId' => is_null($discount) ? null : $discount->getId(),
                    ]
                ),
                'method'          => 'POST',
            ]
        );

        return $this->render(
            '@MiguelAlcainoMindbodyPayments/product/edit.html.twig',
            [
                'product'          => $product,
                'discountForm'     => $discountForm->createView(),
                'discount'         => $discount,
                'currentCustomers' => $currentCustomers,
            ]
        );
    }

    /**
     * @Route("/synchronize/now", name="admin_product_synchronize")
     */
    public function synchronizeProductsAction(MindbodyService $mindBodyService, EntityManagerInterface $manager)
    {
        $services = $mindBodyService->getFormattedServices();

        $newProducts     = [];
        $removedProducts = [];

        $products = $manager->getRepository(Product::class)->findAll();

        foreach ($products as $product) {
            $isThere = false;
            foreach ($services as $service) {
                if ($product->getMerchantId() == $service['id']) {
                    $isThere = true;
                }
            }
            if (!$isThere) {
                $removedProducts[] = $product;
                $product->setIsDeleted(true);
                $manager->persist($product);
            }
        }

        foreach ($services as $service) {
            $product = $manager->getRepository(Product::class)->findOneBy(
                [
                    'merchantId' => $service['id'],
                ]
            );
            if (is_null($product)) {
                $product = (new Product())
                    ->setName($service['name'])
                    ->setPrice($service['price'])
                    ->setMerchantId($service['id']);
                $manager->persist($product);

                $newProducts[] = $product;
            }
        }

        $manager->flush();

        $this->addFlash('notice', 'Los servicios han sido sincronizados correctamente con Mindbody');

        return $this->redirectToRoute('admin_product_index');
    }

}
