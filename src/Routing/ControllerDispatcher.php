<?php

namespace Fenix\Routing;

use Fenix\Http\Message\Response;
use Fenix\Container\Container;

class ControllerDispatcher
{

    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     *Dispatch a request to a given controller and method
     *
     * @param Route $route
     * @param mixed $controller
     * @param string $method
     * @return mixed
     * @throws \ReflectionException
     */
    public function dispatch(Route $route, $controller, $method)
    {
        $parameters = $this->resolveMethodAndClassDependencies(
            $controller, $method, $route->getParameters()
        );
        if (method_exists($controller, 'callAction')) {
            return $controller->callAction($method, $parameters);
        }
        return $this->makeResponse($controller->{$method}(...array_values($parameters)));
    }

    /**
     * Make a new response
     *
     * @param $call
     * @return Response
     */
    public function makeResponse($call)
    {
        return new Response(http_response_code(), [], $call);
    }

    /**
     * Resolve Method and Class Dependencies
     *
     * @param mixed $controller
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    public function resolveMethodAndClassDependencies($class, $action, $parameters = [])
    {
        return $this->container->solveMethod(compact('class', 'action'), $parameters)
                                ->getSolvedParameters();
    }
}