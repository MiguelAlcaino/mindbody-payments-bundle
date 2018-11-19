<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 22/09/18
 * Time: 20:20
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Exception;

use Throwable;

class InvalidItemInShoppingCartException extends MindbodyException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}