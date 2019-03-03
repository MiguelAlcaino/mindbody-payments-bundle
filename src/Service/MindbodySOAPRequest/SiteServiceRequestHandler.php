<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\MindbodySOAPRequest;

use MiguelAlcaino\MindbodyPaymentsBundle\Service\MB_API;
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
     * SiteServiceRequest constructor.
     *
     * @param CacheInterface $cache
     * @param MB_API         $mb
     */
    public function __construct(CacheInterface $cache, MB_API $mb)
    {
        $this->cache = $cache;
        $this->mb    = $mb;
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