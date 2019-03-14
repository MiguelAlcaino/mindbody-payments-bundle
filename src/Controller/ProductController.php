<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Discount;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Product;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\DiscountType;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\ProductType;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\CustomerRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\ProductRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyRequestHandler\SaleServiceRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Product controller.
 *
 * @Route("product")
 */
class ProductController extends AbstractController
{
    /**
     * Lists all product entities.
     *
     * @Route("/", name="admin_product_index", methods={"GET"})
     * @param ProductRepository $productRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy(
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
     * @param Request            $request
     * @param Product            $product
     * @param ProductRepository  $productRepository
     *
     * @param CustomerRepository $customerRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editAction(Request $request, Product $product, ProductRepository $productRepository, CustomerRepository $customerRepository)
    {
        $manager = $this->getDoctrine()->getManager();

        $currentCustomers = $customerRepository->getCurrentCustomersOfProduct($product);
        $editForm         = $this->createForm(ProductType::class, $product);
        $editForm->handleRequest($request);

        /** @var Discount $mainDiscount */
        $mainDiscount = $productRepository->getMainDiscount($product);

        if ($mainDiscount !== null) {
            $discount        = $mainDiscount;
            $defaultProducts = $productRepository->getActiveProductsOfDiscount($discount);
        } else {
            $discount        = null;
            $defaultProducts = new ArrayCollection();
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
                        'discountId' => $discount === null ? null : $discount->getId(),
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
     * @param SaleServiceRequestHandler $saleServiceRequestHandler
     * @param EntityManagerInterface    $manager
     * @param ProductRepository         $productRepository
     *
     * @param TranslatorInterface       $translator
     *
     * @return RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function synchronizeProductsAction(
        SaleServiceRequestHandler $saleServiceRequestHandler,
        EntityManagerInterface $manager,
        ProductRepository $productRepository,
        TranslatorInterface $translator
    )
    {
        $services = $saleServiceRequestHandler->getFormattedServices();

        $newProducts     = [];
        $removedProducts = [];

        /** @var Product[] $products */
        $products = $productRepository->findAll();

        foreach ($products as $product) {
            $isThere = false;
            foreach ($services as $service) {
                if ($product->getMerchantId() === (string)$service['id']) {
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
            $product = $productRepository->findOneBy(
                [
                    'merchantId' => $service['id'],
                ]
            );
            if ($product === null) {
                $product = (new Product())
                    ->setName($service['name'])
                    ->setPrice($service['price'])
                    ->setMerchantId($service['id']);
                $manager->persist($product);

                $newProducts[] = $product;
            }
        }

        $manager->flush();

        $this->addFlash('notice', $translator->trans('notice.products_successfully_synchronized'));

        return $this->redirectToRoute('admin_product_index');
    }

}
