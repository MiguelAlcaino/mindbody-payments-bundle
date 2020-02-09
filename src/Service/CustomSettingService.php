<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use MiguelAlcaino\MindbodyPaymentsBundle\Entity\CustomSetting;
use MiguelAlcaino\MindbodyPaymentsBundle\Repository\CustomSettingRepository;

class CustomSettingService
{
    /**
     * @var CustomSettingRepository
     */
    private $customSettingRepository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * CustomSettingService constructor.
     *
     * @param CustomSettingRepository $customSettingRepository
     * @param EntityManagerInterface  $manager
     */
    public function __construct(CustomSettingRepository $customSettingRepository, EntityManagerInterface $manager)
    {
        $this->customSettingRepository = $customSettingRepository;
        $this->manager                 = $manager;
    }

    /**
     * @param string $key
     * @param string $formType possible values: text|textarea|number
     * @param mixed  $value
     *
     * @return mixed
     */
    public function saveSetting(string $key, string $formType, $value)
    {
        $setting = $this->customSettingRepository->findOneBy(
            [
                'code' => $key,
            ]
        );

        if (null === $setting) {
            $setting = new CustomSetting();
        }

        $setting
            ->setFormType($formType)
            ->setValue(
                [
                    'value' => $value,
                ]
            );

        $this->manager->persist($setting);
        $this->manager->flush();

        return $value;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getSetting(string $key)
    {
        $setting = $this->customSettingRepository->findOneBy(
            [
                'code' => $key,
            ]
        );

        return null !== $setting ? $setting->getValue()['value'] : null;
    }
}
