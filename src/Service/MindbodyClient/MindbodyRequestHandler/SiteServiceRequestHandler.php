<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodyRequestHandler;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MB_API;
use MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodyClient\MindbodySOAPRequest\SiteServiceSOAPRequest;
use Psr\SimpleCache\CacheInterface;

class SiteServiceRequestHandler
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var MB_API
     */
    private $mb;

    /**
     * @var SiteServiceSOAPRequest
     */
    private $siteServiceSOAPRequest;

    /**
     * SiteServiceRequestHandler constructor.
     *
     * @param CacheInterface         $cache
     * @param MB_API                 $mb
     * @param SiteServiceSOAPRequest $siteServiceSOAPRequest
     */
    public function __construct(CacheInterface $cache, MB_API $mb, SiteServiceSOAPRequest $siteServiceSOAPRequest)
    {
        $this->cache                  = $cache;
        $this->mb                     = $mb;
        $this->siteServiceSOAPRequest = $siteServiceSOAPRequest;
    }

    /**
     * Returns an array with id and name of the different Mindbody locations
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFormattedLocations(): array
    {
        $locations          = $this->siteServiceSOAPRequest->getLocations();
        $formattedLocations = [];
        if (array_key_exists('SiteID', $locations['GetLocationsResult']['Locations']['Location'])) {
            $formattedLocations = [
                [
                    'id'   => $locations['GetLocationsResult']['Locations']['Location']['ID'],
                    'name' => $locations['GetLocationsResult']['Locations']['Location']['Name'],
                ],
            ];
        } else {
            foreach ($locations['GetLocationsResult']['Locations']['Location'] as $location) {
                $formattedLocations[] = [
                    'id'   => $location['ID'],
                    'name' => $location['Name'],
                ];
            }
        }

        return $formattedLocations;
    }

    /**
     * Returns a formatted array of Online's Mindbody Programs
     *
     * @param bool $useCache
     *
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getPrograms($useCache = true)
    {
        if ($useCache && $this->cache->has('mindbody.site.programs')) {
            $formattedPrograms = $this->cache->get('mindbody.site.programs');
        } else {
            $programs = $this->mb->GetPrograms(
                [
                    'OnlineOnly'   => true,
                    'ScheduleType' => 'All',
                ]
            );

            $formattedPrograms = [];

            if (array_key_exists('ID', $programs['GetProgramsResult']['Programs']['Program'])) {
                $formattedPrograms = [
                    [
                        'id'           => $programs['GetProgramsResult']['Programs']['Program']['ID'],
                        'name'         => $programs['GetProgramsResult']['Programs']['Program']['Name'],
                        'scheduleType' => $programs['GetProgramsResult']['Programs']['Program']['ScheduleType'],
                        'cancelOffset' => $programs['GetProgramsResult']['Programs']['Program']['CancelOffset'],
                    ],
                ];
            } else {
                foreach ($programs['GetProgramsResult']['Programs']['Program'] as $program) {
                    $formattedPrograms[] = [
                        'id'           => $program['ID'],
                        'name'         => $program['Name'],
                        'scheduleType' => $program['ScheduleType'],
                        'cancelOffset' => $program['CancelOffset'],
                    ];
                }
            }
            $this->cache->set('mindbody.site.programs', $formattedPrograms, 604800);
        }

        return $formattedPrograms;
    }
}