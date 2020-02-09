<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\Program;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\ProgramRepository;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodyRequestHandler\SiteServiceRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class ProgramController extends AbstractController
{
    /**
     * @Route("/sync-programs", name="program_sync_programs")
     * @param SiteServiceRequestHandler $siteServiceRequestHandler
     * @param ProgramRepository         $programRepository
     * @param EntityManagerInterface    $manager
     *
     * @param TranslatorInterface       $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function syncPrograms(
        SiteServiceRequestHandler $siteServiceRequestHandler,
        ProgramRepository $programRepository,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ) {
        $programs = $siteServiceRequestHandler->getPrograms();

        $dbPrograms = $programRepository->findAll();

        foreach ($dbPrograms as $dbProgram) {
            $isThere = false;
            foreach ($programs as $program) {
                if ($dbProgram->getMerchantId() === (string)$program['id']) {
                    $isThere = true;
                }
            }
            if (!$isThere) {
                $dbProgram->setIsDeleted(true);
                $manager->persist($dbProgram);
            }
        }

        foreach ($programs as $program) {
            $dbProgram = $programRepository->findOneBy(
                [
                    'merchantId' => $program['id'],
                ]
            );
            if ($dbProgram === null) {
                $dbProgram = (new Program())
                    ->setName($program['name'])
                    ->setMerchantId($program['id']);
                $manager->persist($dbProgram);
            }
        }

        $manager->flush();

        $this->addFlash('notice', $translator->trans('notice.programs_successfully_synchronized'));

        return $this->redirectToRoute('admin_settings');
    }
}
