<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 19/04/18
 * Time: 22:59
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Exception;

use Throwable;

class NotValidLoginException extends MindbodyException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}