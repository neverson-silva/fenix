<?php

namespace Fenix\Routing;

/**
 * Class Payload
 * @package Nosreven
 * @subpackage Routing
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 * @licence MIT
 */
class Payload
{
    // Cannor be instantiate
    private function __construct()
    {
    }
    /**
     * Get payload request from ajax requests
     *
     * @param string $input Input name
     * @return bool|mixed|null|string
     */
    public static function get($input = null)
    {
        $request = file_get_contents("php://input");
        $request = json_decode($request);
        if ($input !== null) {
            if (is_array($request)) {
                return $request[$input];
            } elseif (is_object($request)) {
                return $request->$input;
            }
        }
        return $request;
    }
    /**
     * Get all requisitions input from payload
     *
     * @return mixed
     */
    public static function all()
    {
        return static::get();
    }
}
