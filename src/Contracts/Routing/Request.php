<?php

namespace Fenix\Contracts\Routing;

/**
 * Handling request
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Meltdown\Contracts\Request
 * @version 0.1
 * @copyright GPL © 2018
 *
 */
interface Request
{
    /**
     * Get all data from get and post requisitions
     *
     * @return array
     */
    public function all() : array;
    /**
     * Get an input request
     *
     * @param string $input
     * @return void
     */
    public function input($input);
    /**
     * Get payload request from ajax requests
     *
     * @param $input
     * @return mixed
     */
    public function get($input);
    /**
     * Get all items in a payload request
     *
     * @return mixed
     */
    public function getAll();
}