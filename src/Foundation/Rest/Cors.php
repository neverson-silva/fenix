<?php

namespace Fenix\Foundation\Rest;

/**
 * Enabling cors
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Meltdown\Foundation\Rest;
 * @version 1.0
 * @copyright MIT © 2018
 *
 */
class Cors
{
    /**
     * Enable cors
     *
     * @return void
     */
    public function allow()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
    }
}