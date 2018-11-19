<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 15/01/18
 * Time: 00:10
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Service\Exception;


use Throwable;

class MindbodyServiceException extends \Exception
{
    private $merchantResponse;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getMerchantResponse()
    {
        return $this->merchantResponse;
    }

    /**
     * @param mixed $merchantResponse
     * @return MindbodyServiceException
     */
    public function setMerchantResponse($merchantResponse)
    {
        $this->merchantResponse = $merchantResponse;
        return $this;
    }


}