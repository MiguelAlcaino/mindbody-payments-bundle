<?php

declare(strict_types=1);

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\Factory;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MB_API;

class MbApiFactory
{
    /** @var string */
    private $sourceName;

    /** @var string */
    private $password;

    /** @var int[] */
    private $siteIds;

    public function __construct(string $sourceName, string $password, array $siteIds)
    {
        $this->sourceName = $sourceName;
        $this->password   = $password;
        $this->siteIds    = $siteIds;
    }

    public function create()
    {
        return new MB_API(
            [
                'SourceName' => $this->sourceName,
                'Password'   => $this->password,
                'SiteIDs'    => $this->siteIds,
            ]
        );
    }
}
