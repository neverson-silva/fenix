<?php

namespace Fenix\Contracts\Routing;

/**
 * Interface UrlParameter
 * @package Core\Contratos\Roteamento
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */
interface UrlParameter
{
    /**
     * Get parameters and action
     * @param $url
     * @param boolean $getAction
     */
    public function matchParameters($url);
}