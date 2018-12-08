<?php
namespace Fenix\Contracts\Support;

/**
 *
 * @author Neverson Silva
 */
interface Jsonable
{
    /**
     * Return a json representation of the object
     * @return string The object
     */
    public function toJson($options = 0);
}