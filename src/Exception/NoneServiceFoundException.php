<?php

/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 13/05/18
 * Time: 20:26
 */

namespace MiguelAlcaino\MindbodyPaymentsBundle\Exception;


class NoneServiceFoundException extends MindbodyException
{

    private $route;

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }
}