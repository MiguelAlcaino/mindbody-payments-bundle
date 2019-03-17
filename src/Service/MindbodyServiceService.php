<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use MiguelAlcaino\MindbodyPaymentsBundle\Exception\NoneServiceFoundException;

class MindbodyServiceService
{
    /**
     * @var FromSessionService
     */
    private $fromSessionService;

    /**
     * @var MindbodyProgramService
     */
    private $mindbodyProgramService;

    /**
     * MindbodyServiceService constructor.
     *
     * @param FromSessionService     $fromSessionService
     * @param MindbodyProgramService $mindbodyProgramService
     */
    public function __construct(FromSessionService $fromSessionService, MindbodyProgramService $mindbodyProgramService)
    {
        $this->fromSessionService     = $fromSessionService;
        $this->mindbodyProgramService = $mindbodyProgramService;
    }

    public function getServices()
    {
        $programIds = $this->mindbodyProgramService->getProgramsIds($this->fromSessionService->getMindbodyClassType());

        try {
            if ($this->fromSessionService->getMindbodyClassType() === 'enrollment') {
                $services = $this->get('mind_body_service')->getFormattedServices(
                    false,
                    $programIds,
                    $request->getSession()->get('mindbody_class_ID')
                );
            } else {
                $services = $this->get('mind_body_service')->getFormattedServices(false, $programIds);
            }
        } catch (NoneServiceFoundException $exception) {
            $this->get('logger')->error('None service has been found');
            $routeParams = [
                'className'      => $request->getSession()->get('className'),
                'teacherName'    => $request->getSession()->get('teacherName'),
                'classStartTime' => $request->getSession()->get('classStartTime'),
                'classEndTime'   => $request->getSession()->get('classEndTime'),
                'classId'        => $request->getSession()->get('mindbody_class_ID'),
            ];

            if ($request->getSession()->has('classType') && $request->getSession()->get('classType') === 'enrollment') {
                $routeParams['classType'] = 'enrollment';
            }

            $exception->setRoute($this->redirectToRoute('widget_book_summary', $routeParams));
            throw $exception;
        }

        return $services;
    }
}