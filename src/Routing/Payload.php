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

        if (is_string($request) && stripos($request, '&') || stripos($request, '=')) {
           $request = static::parseBody(urldecode($request));
        } else {
            $request = json_decode($request);
        }

        if ($input !== null) {
            if (is_array($request)) {
                return $request[$input] ?? null;
            } elseif (is_object($request)) {
                return $request->$input ?? null;
            }
        }
        return $request;
    }

    private static function parseBody($body)
    {
        if (stripos($body, '&')) {
            $firstLevel = explode('&', $body);

            $parsed = [];

            foreach ($firstLevel as $value) {
                $secondLevel = explode('=', $value);
                $parsed[$secondLevel[0]] = $secondLevel[1];
            }
            return $parsed;
        } else {
            $explode = explode('=', $body);

            return [ $explode[0] => $explode[1] ];
        }

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
