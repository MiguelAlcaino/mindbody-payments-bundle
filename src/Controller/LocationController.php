<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Location;
use MiguelAlcaino\MindbodyPaymentsBundle\Form\LocationType;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\LocationRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodyRequestHandler\SiteServiceRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin/location")
 */
class LocationController extends AbstractController
{
    /**
     * @Route("/", name="location_index", methods={"GET"})
     * @param LocationRepository $locationRepository
     *
     * @return Response
     */
    public function index(LocationRepository $locationRepository): Response
    {
        return $this->render(
            '@MiguelAlcainoMindbodyPayments/location/index.html.twig',
            [
                'locations' => $locationRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="location_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $location = new Location();
        $form     = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($location);
            $entityManager->flush();

            return $this->redirectToRoute('location_index');
        }

        return $this->render(
            '@MiguelAlcainoMindbodyPayments/location/new.html.twig',
            [
                'location' => $location,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="location_show", methods={"GET"})
     */
    public function show(Location $location): Response
    {
        return $this->render(
            '@MiguelAlcainoMindbodyPayments/location/show.html.twig',
            [
                'location' => $location,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="location_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Location $location): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'location_index',
                [
                    'id' => $location->getId(),
                ]
            );
        }

        return $this->render(
            '@MiguelAlcainoMindbodyPayments/location/edit.html.twig',
            [
                'location' => $location,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="location_delete", methods={"DELETE"})
     * @param Request  $request
     * @param Location $location
     *
     * @return Response
     */
    public function delete(Request $request, Location $location): Response
    {
        if ($this->isCsrfTokenValid('delete' . $location->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($location);
            $entityManager->flush();
        }

        return $this->redirectToRoute('location_index');
    }

    /**
     * @Route("/synchronize/now", name="location_synchronize")
     * @param SiteServiceRequestHandler $siteServiceRequestHandler
     * @param EntityManagerInterface    $manager
     *
     * @param LocationRepository        $locationRepository
     *
     * @param TranslatorInterface       $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function synchronizeProductsAction(
        SiteServiceRequestHandler $siteServiceRequestHandler,
        EntityManagerInterface $manager,
        LocationRepository $locationRepository,
        TranslatorInterface $translator
    )
    {
        $mindbodyLocations = $siteServiceRequestHandler->getFormattedLocations();

        $locations = $locationRepository->findAll();

        foreach ($locations as $location) {
            $isThere = false;
            foreach ($mindbodyLocations as $mindbodyLocation) {
                if ($location->getMerchantId() === (string)$mindbodyLocation['id']) {
                    $isThere = true;
                }
            }
            if (!$isThere) {
                $location->setIsDeleted(true);
                $manager->persist($location);
            }
        }

        foreach ($mindbodyLocations as $mindbodyLocation) {
            $location = $locationRepository->findOneBy(
                [
                    'merchantId' => $mindbodyLocation['id'],
                ]
            );
            if ($location === null) {
                $location = (new Location())
                    ->setName($mindbodyLocation['name'])
                    ->setMerchantId($mindbodyLocation['id']);
                $manager->persist($location);
            }
        }

        $manager->flush();

        $this->addFlash('notice', $translator->trans('notice.locations_successfully_synchronized'));

        return $this->redirectToRoute('location_index');
    }
}
