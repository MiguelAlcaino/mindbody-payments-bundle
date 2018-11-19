<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 22/09/18
 * Time: 21:37
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Exception;


use Throwable;

class NoProgramsInTransactionRecordException extends MindbodyException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}