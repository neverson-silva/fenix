<?php

namespace Fenix\Container;

use Fenix\Contracts\Container\Container as ContainerContract;
use Fenix\Traits\Container\ContainerTrait;
use Fenix\Container\EntryNotFoundException;
use Fenix\Container\ContainerException;
use Fenix\Http\Message\Response;
use Fenix\Support\Collection;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionMethod;
use ReflectionClass;
use Exception;
use Reflector;

/**
 * Description of Container
 *
 * @author Neverson Silva
 */
class Container implements ContainerContract
{
    use ContainerTrait;

    /**
     *
     * @var Collection
     */
    protected $container;

    /**
     *
     * @var Response
     */
    protected $response;

    private static $instance;

    private function __construct(Collection $collection)
    {
        $this->container = $collection;
    }

    public static function getContainer()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self(new Collection);
        }
        return self::$instance;
    }

    /**
     * Bind and object to container
     *
     * @param string $key
     * @param mixed $object
     * @param array $params
     * @return void
     * @throws \Fenix\Container\ContainerException
     * @throws \ReflectionException
     */
    public function bind(string $key = '', $object, $params = []): void
    {
        if ($this->has($key)) {
            throw new ContainerException("The container already has a '$key' value.");
        }

        if (is_string($object)) {
            $object = $this->solveClass($object, $params);
        }
        $this->container->add($key, $object);
    }

    public function forget($id): void
    {
        $this->container->forget($id);
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        return $this->container->hasKey($id);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasInstance($id)
    {
        return $this->container->hasInstance($id);
    }

    /**
     * Get instance from container
     * @param $id Full path object
     * @return bool|mixed
     */
    public function getInstance($id)
    {
        foreach($this->getDependencies() as $dependency) {
            $class = get_class($dependency);
            if ($class == $id) {
                return $dependency;
            }
        }
        return false;
    }

    /**
     * Return all objetos within the container
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->container->toArray();
    }

    /**
     * Resolve paramaters dependencies
     *
     * @param array $dependencies
     * @param array $paramaters
     * @return Response
     */
    public function respond($action, array $paramaters): \Fenix\Contracts\Container\Response
    {

    }

    /**
     * Resolve class dependencies
     *
     * @param  $class
     * @param array $dependencies
     * @return null|object
     * @throws \ReflectionException
     */
    public function solveClass($class, $dependencies = [])
    {
        if ($this->has($class)) {
            return $this->get($class);
        }
        if ($this->hasInstance($class)) {
            return $this->getInstance($class);
        }
        $reflected = new ReflectionClass($class);

        if (!$reflected->isInstantiable()) {
            throw new \ReflectionException("{$reflected->name} is not instantiable");
        }
        $constructor = $reflected->getConstructor();

        if (!$constructor || $constructor == null) {
            $new = new $reflected->name;
            $objectProperties = $this->getClassProperties($reflected->getProperties());

            if (!$objectProperties->isEmpty()) {

                $objectProperties->onEach(function($op, $key) use($reflected, $new){
                    $property = $reflected->getProperty($key);
                    $property->setAccessible(true);
                    $property->setValue($new, $this->solveClass($op));
                });
            }

        } else {
            $parameters = $this->inject($constructor, $dependencies);
            $new = $parameters instanceof Reflector
                ? null
                : $reflected->newInstanceArgs($parameters);
        }
        if (!$this->hasInstance(get_class($new))) {
            $this->container->add(get_class($new), $new);
        }
        return $new;
    }

    /**
     * Get class compositions
     * @param array $properties
     * @return Collection
     */
    private function getClassProperties(array $properties): Collection {

        $props = new Collection();
        $properties = new Collection($properties);
        $properties->filter(fn(ReflectionProperty $prop) => !$prop->getType()->isBuiltin());
        if (!$properties->isEmpty() ) {
            $properties->onEach(function (ReflectionProperty $property) use($props){
                $key = $property->getName();
                $type = $property->getType()->getName();
                $props->put($key, $type);
            });
        }

        return $props;
    }

    /**
     * Resolve methods/functions dependencies
     *
     * @param $method
     * @param array $dependencies
     * @return Container
     * @throws \ReflectionException
     */
    public function solveMethod($method, $dependencies = [])
    {
        $dependencies = is_array($dependencies) ? $dependencies : [$dependencies];

        if (is_array($method)) {
            $controller = $this->controller($method);
            $info = new ReflectionMethod($controller['class'], $controller['method']);
        } else {
            $info = new ReflectionFunction($method);
        }


        $parameters = $this->inject($info, $dependencies);
        if ($info instanceof ReflectionMethod) {
            $this->response = [$controller, $parameters];
            $this->response = [$controller, $parameters];
        } else {
            $this->response = [$info->getClosure(), $parameters];
        }
        return $this;
    }

    public function valid(array $d)
    {
        return empty($d) || is_string(array_keys($d)[0]);
    }

    private function nameParameters(array $parameters)
    {
        $named = [];

        foreach($parameters as $parameter) {
            $named[$parameter->name] = $parameter;
        }

        return $named;
    }

    private function inject(Reflector $reflector, $dependencies)
    {
        $dependencies = is_array($dependencies) ? $dependencies : [$dependencies];

        if (!$this->valid($dependencies)) {
            $dependencies = $this->nameParameters($reflector->getParameters());
        }
        $parameters = $reflector->getParameters();

        $depend = $this->matchParameters($parameters, $dependencies);

        return $this->resolveParameters($parameters, $depend);
    }

    /**
     * Call the action
     * @param string|array $action
     * @param array $parameters
     * @return mixed
     */
    public function call($action = null, $parameters = null)
    {
        $action = $action ?? $this->response[0];
        $parameters = $parameters ?? $this->response[1];
        $this->response = null;
        return $this->respondWithAction($action, $parameters);
    }

    /**
     * Respond and call the action performed.
     *
     * @param string|array $action
     * @param array $parameters
     * @return mixed
     */
    public function respondWithAction($action, array $parameters)
    {
        if (is_array($action)) {
            return new Response(http_response_code(), [],
                $action['class']->{$action['method'](...array_values($parameters))});
        } else {
            return new Response(http_response_code(), [], $action(...array_values($parameters)));
        }
    }

    public function getSolvedParameters()
    {
        return isset($this->response[1]) ? $this->response[1] : null;

    }

}
