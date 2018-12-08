<?php

namespace Fenix\Contracts\Request;

/**
 * Base controller
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Fenix\Contracts\Request
 * @version 1.0
 * @copyright MIT © 2018
 *
 */
interface Controller
{
    
    /**
     * Render a view
     * @param string $view
     * @param array $params
     * @return response
     */
    public function render($view, $params = []);
    
    
    public function middleware($name);
    
    /**
     *
     * @param string $action
     * @param array $params
     * @return Meltdown\HttpMessage\Response
     */
    public function callAction(string $action, array $params);
}