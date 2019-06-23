<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\TransactionRecord;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\RefundType;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\TransactionRecordRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyService;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\Paginator;
use MiguelAlcaino\PaymentGateway\Interfaces\RefundHandlerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Transactionrecord controller.
 *
 * @Route("admin/transactions")
 */
class TransactionRecordController extends AbstractController
{
    /**
     * Lists all transactionRecord entities.
     *
     * @param Request $request
     *
     * @return Response
     * @Route("/", name="admin_transactions_index", methods={"GET"})
     */
    public function indexAction(Request $request, TransactionRecordRepository $transactionRecordRepository, ParameterBagInterface $parameterBag)
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


        $transactionRecords = $transactionRecordRepository
            ->getAllBy($criteria, $limit, ($offset - 1) * $limit);

        $totalTransactionRecords = $transactionRecordRepository->countBy($criteria);

        $paginator = new Paginator(
            $totalTransactionRecords,
            $offset,
            $limit,
            $midRange
        );

        $viewParams['transactionRecords'] = $transactionRecords;
        $viewParams['searchedValue']      = $searchedValue;
        $viewParams['paginator']          = $paginator;

        return $this->render('@MiguelAlcainoMindbodyPayments/transactionrecord/index.html.twig', $viewParams);
    }

    /**
     * Finds and displays a transactionRecord entity.
     *
     * @param TransactionRecord $transactionRecord
     *
     * @return Response
     * @Route("/{id}", name="admin_transactions_show", methods={"GET"})
     */
    public function showAction(EntityManagerInterface $manager, int $id)
    {
        $transactionRecord              = $manager->getRepository(TransactionRecord::class)->find($id);
        $interval                       = $transactionRecord->getCreated()->diff(new \DateTime());
        $arrayView['transactionRecord'] = $transactionRecord;

        if ((int)$interval->days === 0) {
            $refundForm              = $this->createForm(
                RefundType::class,
                null,
                [
                    'action' => $this->generateUrl(
                        'admin_transactions_refund',
                        [
                            'id' => $transactionRecord->getId(),
                        ]
                    ),
                    'method' => 'POST',
                ]
            );
            $arrayView['refundForm'] = $refundForm->createView();
        }

        return $this->render('@MiguelAlcainoMindbodyPayments/transactionrecord/show.html.twig', $arrayView);
    }

    /**
     * @param EntityManagerInterface $manager
     * @param int                    $id
     * @param RefundHandlerInterface $refundHandler
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @Route("/refund/{id}", name="admin_transactions_refund", methods={"POST"})
     */
    public function refundAction(EntityManagerInterface $manager, int $id, RefundHandlerInterface $refundHandler)
    {
        $request           = $this->get('request_stack')->getCurrentRequest();
        $transactionRecord = $manager->getRepository(TransactionRecord::class)->find($id);

        $refundForm = $this->createForm(
            RefundType::class,
            null,
            [
                'action' => $this->generateUrl(
                    'admin_transactions_refund',
                    [
                        'id' => $transactionRecord->getId(),
                    ]
                ),
                'method' => 'POST',
            ]
        );

        $refundForm->handleRequest($request);

        if ($refundForm->isSubmitted() && $refundForm->isValid()) {
            $refundHandler->refund($transactionRecord);
            $manager->persist($transactionRecord);
            $manager->flush();

            $this->addFlash('notice', 'El pago ha sido reembolsado');

            return $this->redirectToRoute(
                'admin_transactions_show',
                [
                    'id' => $transactionRecord->getId(),
                ]
            );
        } else {
            throw new \Exception('No deberias estar aca');
        }
    }

    /**
     * @Route("/download/excel", name="download_excel")
     */
    public function downloadExcelAction(Request $request, TransactionRecordRepository $transactionRecordRepository, ParameterBagInterface $parameterBag)
    {
        $spreadsheet   = new Spreadsheet();
        $filename      = '/tmp/cyglo-transactions-' . rand(0, 100) . '.xlsx';
        $response      = new Response();
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

        $em = $this->getDoctrine()->getManager();

        $transactionRecords = $transactionRecordRepository->getForExcel($criteria, false);
        $newTransactions    = [];
        $newTransactions[]  = [
            'id',
            'Nombre',
            'Apellido',
            'Email',
            'Nombre en la tarjeta',
            'Ultimos 4 digitos',
            'Tipo de documento',
            '# Documento',
            'Pais',
            'Departamento',
            'Ciudad',
            'Servicio',
            'Estudio',
            'Monto',
            'IVA',
            'Fecha de compra',
            'Fecha de expiracion',
            'Refund Date',
            'Status',
            'Cuotas',
            'Merchant Purchase Id',
            'Merchant Id',
            'Codigo de autorizacion bancario',
        ];
        /**
         * @var int               $key
         * @var TransactionRecord $transactionRecord
         */
        foreach ($transactionRecords as $key => $transactionRecord) {
            $newTransactions[] = [
                'id'                 => $transactionRecord->getId(),
                'name'               => $transactionRecord->getCustomer()->getFirstName(),
                'lastname'           => $transactionRecord->getCustomer()->getLastName(),
                'email'              => $transactionRecord->getCustomer()->getEmail(),
                'cardholdername'     => $transactionRecord->getCreditCardHolderName(),
                'cardlastfour'       => $transactionRecord->getCreditCardLastFourDigits(),
                'documento'          => $transactionRecord->getDocumentType(),
                'ndocumento'         => $transactionRecord->getDocumentNumber(),
                'pais'               => $transactionRecord->getUserCountry(),
                'state'              => $transactionRecord->getUserState(),
                'city'               => $transactionRecord->getUserCity(),
                'servicio'           => $transactionRecord->getTransactionItems()[0] != null ? $transactionRecord->getTransactionItems()[0]->getName()
                    : '-',
                'estudio'            => $transactionRecord->getUserPreferredLocation(),
                'amount'             => $transactionRecord->getAmount(),
                'taxAmount'          => $transactionRecord->getTaxAmount(),
                'created'            => $transactionRecord->getCreated()->format('d-m-Y'),
                'expiration'         => is_null($transactionRecord->getServiceExpirationDate())
                    ? ''
                    : $transactionRecord->getServiceExpirationDate()
                        ->format('d-m-Y'),
                'refundDate'         => is_null($transactionRecord->getRefundDate()) ? '' : $transactionRecord->getRefundDate()->format('d-m-Y'),
                'status'             => $transactionRecord->getStatus(),
                'installments'       => $transactionRecord->getInstallments(),
                'merchantPurchaseId' => $transactionRecord->getMerchantPurchaseId(),
                'merchantId'         => $transactionRecord->getMerchantId(),
                'authorizationCode'  => $transactionRecord->getAuthorizationCode(),
            ];
        }
        try {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($newTransactions);
            //            $sheet->setCellValue('A1', 'Hello World !');
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setAutoSize(true);
            $sheet->getColumnDimension('M')->setAutoSize(true);
            $sheet->getColumnDimension('N')->setAutoSize(true);
            $sheet->getColumnDimension('O')->setAutoSize(true);
            $sheet->getColumnDimension('P')->setAutoSize(true);
            $sheet->getColumnDimension('Q')->setAutoSize(true);
            $sheet->getColumnDimension('R')->setAutoSize(true);
            $sheet->getColumnDimension('S')->setAutoSize(true);
            $sheet->getColumnDimension('T')->setAutoSize(true);
            $sheet->getColumnDimension('U')->setAutoSize(true);
            $sheet->getColumnDimension('V')->setAutoSize(true);
            $sheet->getColumnDimension('W')->setAutoSize(true);
            $writer = new Xlsx($spreadsheet);
            $writer->save($filename);

            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', mime_content_type($filename));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
            $response->headers->set('Content-length', filesize($filename));

            // Send headers before outputting anything
            $response->sendHeaders();

            $response->setContent(file_get_contents($filename));

            return $response;
        } catch (Exception $exception) {
        }
    }

    /**
     * @param Request         $request
     * @param MindbodyService $mindBodyService
     *
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     * @author malcaino
     * @Route("/repair-transaction", name="admin_transactions_repair_transaction_with_mindbody", methods={"POST"})
     */
    public function repairTransactionWithMindbodyAction(Request $request, MindbodyService $mindBodyService, ParameterBagInterface $parameterBag)
    {
        $manager = $this->getDoctrine()->getManager();
        /** @var TransactionRecord $transactionRecord */
        $transactionRecord       = $manager->getRepository(TransactionRecord::class)->getOneById(
            $request->request->get('id'),
            false
        );
        $mindbodyPaymentMethodId = $transactionRecord->getMindbodyPaymentMethodId();

        if (empty($mindbodyPaymentMethodId)) {
            $mindbodyPaymentMethodId = $parameterBag->get('mindbody_tpaga_id');
        }

        $transactionRecord = $mindBodyService->makePurchase(
            $transactionRecord,
            $transactionRecord->getCustomer()->getMerchantId(),
            $transactionRecord->getServiceId(),
            $mindbodyPaymentMethodId,
            $transactionRecord->getPreAmount(),
            $transactionRecord->getDiscountCode()
        );

        $manager->persist($transactionRecord);
        $manager->flush();

        $mailer  = $this->get('mailer');
        $message = (new \Swift_Message('Tu compra en Cyglo está lista!'))
            ->setFrom($parameterBag->get('mailer_user'), 'Hola Cyglo')
            ->setTo($transactionRecord->getCustomer()->getEmail())
            ->setBody(
                $this->renderView(
                    '@MiguelAlcainoMindbodyPayments/Mail/mindbodyPurchaseFixed.html.twig',
                    [
                        'transactionRecord' => $transactionRecord,
                    ]
                ),
                'text/html'
            );

        $mailer->send($message);

        return new JsonResponse(
            [
                'status' => true,
            ]
        );
    }
}
