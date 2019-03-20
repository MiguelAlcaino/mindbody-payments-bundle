<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\TwigExtension;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PriceFormatExtension extends AbstractExtension
{
    /**
     * @var int
     */
    private $defaultDecimals;

    /**
     * @var string
     */
    private $defaultDecimalPoint;

    /**
     * @var string
     */
    private $defaultThousandSeparator;

    /**
     * PriceFormatExtension constructor.
     *
     * @param ParameterBagInterface $paramBag
     */
    public function __construct(ParameterBagInterface $paramBag)
    {
        if ($paramBag->has('price.default_decimals')) {
            $this->defaultDecimals = $paramBag->get('price.default_decimals');
        }

        if ($paramBag->has('price.default_decimal_point')) {
            $this->defaultDecimalPoint = $paramBag->get('price.default_decimal_point');
        }

        if ($paramBag->has('price.default_thousand_separator')) {
            $this->defaultThousandSeparator = $paramBag->get('price.default_thousand_separator');
        }
    }

    public function getFilters()
    {
        return [
            new TwigFilter('price_format', [$this, 'formatPrice']),
        ];
    }

    public function formatPrice($number)
    {
        $decimals     = 2;
        $decPoint     = '.';
        $thousandsSep = ',';

        if ($this->defaultDecimals !== null) {
            $decimals = $this->defaultDecimals;
        }

        if ($this->defaultDecimalPoint !== null) {
            $decPoint = $this->defaultDecimalPoint;
        }

        if ($this->defaultThousandSeparator !== null) {
            $thousandsSep = $this->defaultThousandSeparator;
        }

        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }
}