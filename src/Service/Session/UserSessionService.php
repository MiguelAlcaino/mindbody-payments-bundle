<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\Session;

class UserSessionService
{
    /**
     * @var FromSessionService
     */
    private $fromSessionService;

    /**
     * UserSessionService constructor.
     *
     * @param FromSessionService $fromSessionService
     */
    public function __construct(FromSessionService $fromSessionService)
    {
        $this->fromSessionService = $fromSessionService;
    }

    /**
     * @return bool
     */
    public function isUserReadyToBookClass(): bool
    {
        if (
            $this->fromSessionService->hasMindbodyClientGUID()
            && $this->fromSessionService->hasMindbodyClientID()
            && $this->fromSessionService->hasMindbodyClientEmail()
            // TODO: Not sure if its needed
            // && $request->getSession()->has('mindbody_client')
            && $this->fromSessionService->hasMindbodyClassId()
            && $this->fromSessionService->hasMindbodyClientCurrentServiceId()
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isUserLoggedIn(): bool
    {
        if (
            $this->fromSessionService->hasMindbodyClientGUID()
            && $this->fromSessionService->hasMindbodyClientID()
            && $this->fromSessionService->hasMindbodyClientEmail()
        ) {
            return true;
        } else {
            return false;
        }
    }
}