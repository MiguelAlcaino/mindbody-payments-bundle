<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Customer;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Customer controller.
 *
 * @Route("customer")
 */
class CustomerController extends AbstractController
{
    /**
     * Lists all customer entities.
     *
     * @Route("/", name="customer_index", methods={"GET"})
     */
    public function indexAction(Request $request, EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {

        $limit         = 50;
        $midRange      = 7;
        $offset        = $request->query->get('page', 1);
        $viewParams    = [];
        $criteria      = [];
        $start         = $request->query->get('start');
        $end           = $request->query->get('end');
        $searchedValue = $request->query->get('search');

        if (!empty($searchedValue)) {
            $criteria['customerName'] = $searchedValue;
        }

        if (!empty($start) && !empty($end)) {
            $viewParams['start'] = $start;
            $viewParams['end']   = $end;

            $startDatetime     = \DateTime::createFromFormat('d-m-Y H:i:s', $start . ' 00:00:00')->setTimezone(
                new \DateTimeZone($parameterBag->get('timezone'))
            );
            $endDatetime       = \DateTime::createFromFormat('d-m-Y H:i:s', $end . '23:59:59')->setTimezone(
                new \DateTimeZone($parameterBag->get('timezone'))
            );
            $criteria['start'] = $startDatetime;
            $criteria['end']   = $endDatetime;
        }

        $customers = $em->getRepository(Customer::class)
            ->getAllBy($criteria, $limit, ($offset - 1) * $limit);

        $totalCustomers = $em->getRepository(Customer::class)
            ->countBy($criteria);

        $paginator = new Paginator(
            $totalCustomers,
            $offset,
            $limit,
            $midRange
        );

        $viewParams['customers']     = $customers;
        $viewParams['searchedValue'] = $searchedValue;
        $viewParams['paginator']     = $paginator;

        return $this->render('@MiguelAlcainoMindbodyPayments/customer/index.html.twig', $viewParams);
    }

    /**
     * Finds and displays a customer entity.
     *
     * @Route("/{id}", name="customer_show", methods={"GET"})
     */
    public function showAction(Customer $customer)
    {
        return $this->render(
            '@MiguelAlcainoMindbodyPayments/customer/show.html.twig',
            [
                'customer' => $customer,
            ]
        );
    }
}
