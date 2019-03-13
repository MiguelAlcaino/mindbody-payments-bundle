<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\Credentials;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MindbodyCredentialsService
{
    /**
     * @var string
     */
    private $sourceName;

    /**
     * @var string
     */
    private $sourcePassword;

    /**
     * @var string
     */
    private $adminUser;

    /**
     * @var string
     */
    private $adminPassword;

    /**
     * @var array
     */
    private $siteIds;

    /**
     * MindbodyCredentialsService constructor.
     *
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->adminUser      = $params->get('mindbody_admin_user');
        $this->adminPassword  = $params->get('mindbody_admin_password');
        $this->sourceName     = $params->get('mindbody_source_name');
        $this->sourcePassword = $params->get('mindbody_source_password');
        $this->siteIds        = $params->get('mindbody_site_ids');
    }

    /**
     * @return string
     */
    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    /**
     * @return string
     */
    public function getSourcePassword(): string
    {
        return $this->sourcePassword;
    }

    /**
     * @return string
     */
    public function getAdminUser(): string
    {
        return $this->adminUser;
    }

    /**
     * @return string
     */
    public function getAdminPassword(): string
    {
        return $this->adminPassword;
    }

    /**
     * @return array
     */
    public function getSiteIds(): array
    {
        return $this->siteIds;
    }
}