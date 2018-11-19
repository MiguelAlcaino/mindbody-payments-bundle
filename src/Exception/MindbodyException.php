<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 19/04/18
 * Time: 23:00
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Exception;


use Throwable;

class MindbodyException extends \Exception
{
    private $context;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}