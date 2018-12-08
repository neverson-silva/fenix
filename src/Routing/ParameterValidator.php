<?php

namespace Fenix\Routing;

use ReflectionFunction;
use ReflectionMethod;
use Closure;
/**
 * Validate arguments
 *
 * Validate arguments from functions and class methods
 *
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 * @package Meltdown
 * @subpackage Foundation\App
 *
 */
class ParameterValidator
{
    //Cannot be Instantiate
    private function __constructor()
    {
    }

    /**
     * Valid arguments name
     *
     * Valid if the arguments name are the same
     *
     * @param string|array $action Action which would be called
     * @param array $params Dependencies
     * @return boolean
     * @throws \ReflectionException
     */
    public static function validateParameterName($action, array $params)
    {
        $reflection = static::getReflection($action);
        $functionParams = $reflection->getParameters();
        $paramsName = array_keys($params);
        for ($i = 0; $i< count($functionParams); $i++) {
            if ($functionParams[$i]->name !== $paramsName[$i]) {
                throw new \LogicException("
                        Arguments name doesn't match. The parameter name registered in the router must have the
                        same name from method/function parameter.
                    ");
            }
        }
        return true;
    }

    /**
     * Valid number of arguments
     *
     * Valid if arguments from the action and the depenencies has the same number.
     *
     * @param string|array $action Action which would be called
     * @param array $params Dependencies
     * @return boolean
     * @throws \ReflectionException
     */
    public static function validNumberParameters($action, array $params)
    {
        $reflection = static::getReflection($action);
        $functionParams = $reflection->getParameters();
        if (count($functionParams) === count($params)) {
            return true;
        }
        $values = ['name' => ' function '. $reflection->name . '()'];
        if ($reflection->isClosure()) {
            $values['name'] = $reflection->name . ' function';
        }
        $values['passed'] = count($params);
        $values['expected'] = $reflection->getNumberOfRequiredParameters();
        $message = vsprintf('Invalid number of parameters to %s, %s passed and exactly %s expected', $values);

        throw new InvalidNumberParameter($message);
    }

    /**
     * Get the a Reflection class
     *
     * @param Closure|string $action Action to be called
     * @return ReflectionFunction|ReflectionMethod
     * @throws \ReflectionException
     */
    public static function getReflection($action)
    {
        if ($action instanceof Closure) {
            return new ReflectionFunction($action);
        }
        if ((strpos($action, '@') === false) && strpos($action, ':') === false) {
            return new ReflectionFunction($action);
        }

        if (strpos($action, '@')) {
            $action = \str_replace('@', '::', $action);
        }
        return new ReflectionMethod($action);
    }
}