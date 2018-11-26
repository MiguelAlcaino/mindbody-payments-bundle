<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\Validator;

use MiguelAlcaino\MindbodyPaymentsBundle\Exception\Validator\AfterTenYearsException;
use MiguelAlcaino\MindbodyPaymentsBundle\Exception\Validator\BeforeCurrentMonthYearException;
use Symfony\Component\Translation\TranslatorInterface;

class WithinTenYearsValidatorService
{
    /**
     * @var TranslatorInterface
     */
    private $translatorService;

    /**
     * WithinTenYearsValidatorService constructor.
     *
     * @param TranslatorInterface $translatorService
     */
    public function __construct(TranslatorInterface $translatorService)
    {
        $this->translatorService = $translatorService;
    }

    /**
     * @param $monthNumber
     * @param $yearNumber
     *
     * @return bool
     * @throws AfterTenYearsException
     * @throws BeforeCurrentMonthYearException
     */
    public function validate($monthNumber, $yearNumber){
        $expirationDate = \DateTime::createFromFormat('Y-m-d H:i:s', $yearNumber . '-' . $monthNumber . '-01 00:00:00');
        $currentMonthYear = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m') . '-01 00:00:00');

        $tenYearsMoreDate = \DateTime::createFromFormat('Y-m-d H:i:s', (date('Y') + 10) . '-' . date('m') . '-01 00:00:00');

        if ($expirationDate->getTimestamp() < $currentMonthYear->getTimestamp()) {
            throw new BeforeCurrentMonthYearException($this->translatorService->trans('mindbody.within_ten_years_validator.before_current_month_year'));
        } else if ($expirationDate->getTimestamp() > $tenYearsMoreDate->getTimestamp()) {
            throw new AfterTenYearsException($this->translatorService->trans('mindbody.within_ten_years_validator.after_ten_years'));
        }

        return true;
    }
}