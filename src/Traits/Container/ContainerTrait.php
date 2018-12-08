<?php

namespace Fenix\Traits\Container;

use Fenix\Container\ContainerException;
use Fenix\Support\Collection;
use ReflectionParameter ;
use Reflector;
/**
 * Description of ContainerTrait
 *
 * @author Neverson Silva
 */
trait ContainerTrait 
{

    protected function matchParameters($parameters, $dependencies = [])
    {
        $matched = [];
        if (count($parameters) == count($dependencies)){
            foreach ($parameters as $key => $parameter) {
                $dependencies = array_values($dependencies);
                $this->addIfNotExist($matched, $parameter->name, $dependencies[$key]);
            }

        } elseif (empty($dependencies)) {
            foreach ($parameters as $key => $parameter) {
                $matched[$parameter->name] = $parameter->name;
            }
        }  else {
            foreach ($parameters as $key => $parameter) {
                $value = $this->getValue($dependencies, $key, $parameter);
                $matched[$parameter->name] = $value;
            }
        }
        return $matched;
    }

    private function getValue(array $dependencies, $key, ReflectionParameter $parameter)
    {
        return $this->getIfExists($dependencies, $parameter->name) ?? $this->getByKey($dependencies, $key, $parameter);
    }

    private function addIfNotExist(&$array, $search, $vaulueToAdd)
    {
        if (!isset($array[$search])) {
            $array[$search] = $vaulueToAdd;
        }
    }

    /**
     * Get a pair keyed value
     * @param $array
     * @param $search
     * @return null|mixed
     */
    private function getIfExists($array, $search)
    {
        if (isset($array[0])){
            return null;
        }
        return $array[$search] ?? $search;
    }

    /**
     * Gey value by integer key
     * @param $array
     * @param $key
     * @param ReflectionParameter $parameter
     * @return mixed
     */
    public function getByKey($array, $key, ReflectionParameter $parameter = null)
    {
        return $array[$key] ?? $parameter->name;
    }


    
    /**
     * Resolve parameters
     *
     * @param array $parameters
     * @param array $depen
     * @return array
     */
    protected function resolveParameters(array $parameters, array $dependencies)
    {
        $matched = new Collection;
        Collection::create($parameters)
                ->map(function($parameter, $key) use($dependencies, $matched) {
                    $dependency = $parameter->getClass();                    
                    $dependency || $dependency !== null
                                ? $matched->put($parameter->name, $this->solveClass($dependency->name, $dependencies))
                                : $matched->put($parameter->name, $this->getDependenciesParameters($parameter, $dependencies));
                });
        return $matched->toArray();
    }
    
    /**
     * Get methods dependecies
     * @param \Reflector $parameter
     * @param array $dependencies
     * @throws \LogicException Case a not valid value is received.
     * @return mixed
     */
    protected function getDependenciesParameters($parameter, $dependencies)
    {
        if (isset($dependencies[$parameter->name])) {
            if ($dependencies[$parameter->name] !== $parameter->name){
                return $dependencies[$parameter->name];
            }
        }
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new \InvalidArgumentException("Parameter [{$parameter->name}] not received a valid value");
    }
        /**
     * Instantiate new controller
     *
     * @param array $controller An associative array with method and controller keys
     *                          passing the name of the controler class and the action
     *                          to be called.
     * @return array
     */
    protected function controller($controller)
    {
        if (isset($controller['controller'])) {
            $class = $controller['controller'];
        } elseif(isset($controller['class'])){
            $class = $controller['class'];
        }
        $method = $controller['action'];
        $class = is_object($class) ? $class : $this->solveClass($class, []);
        return compact('class', 'method');
    }
}
