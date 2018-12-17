<?php

namespace Fenix\Routing;

use Fenix\Http\Message\Response;
use Fenix\Container\Container;
use Fenix\Traits\Macro;

class Route
{
    use Macro;

    private $controllerNamespace;
    /**
     * Instance  of Controller
     *
     * @var
     */
    private $controller;
    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    private $parameterNames;
    /**
     * The array of matched parameters.
     *
     * @var array
     */
    private $parameters;
    /**
     *
     * @var Router
     */
    private $router;
    /**
     *
     * @var Container
     */
    public $container;

    /**
     * Route constructor.
     * @param Router|null $router
     * @param Container|null $container
     * @param null $controllerNamespace
     * @throws \Exception
     */
    public function __construct(Router $router = null, Container $container = null, $controllerNamespace = null)
    {
        $this->controllerNamespace = $controllerNamespace;

        if ($router !== null) {
            $this->setRouter($router);
            $this->initializeRouteSystem();
        }
        if ($container) {
            $this->setContainer($container);
        }


    }

    /**
     * Initialize route system
     *
     * @throws \Exception
     */
    public function initializeRouteSystem()
    {
        $this->router = $this->router->run();
        $this->setParameters();
    }

    /**
     * Set parameters for the route
     * @return void
     */
    private function setParameters()
    {
        if (isset($this->router->getCurrentAction()['parameters'])) {
            $parameters = $this->router->getCurrentAction()['parameters'];
            $this->parameterNames = array_keys($parameters);
            $this->parameters = $parameters;
        }
    }

    /**
     * Run routing system
     *
     * @return mixed
     * @throws \Exception
     */
    public function bootstrap()
    {
        if ($this->hasRoute() && $this->hasAction()) {
            try {
                if ($this->actionIsController()) {
                    return $this->runController();
                }
                return $this->runCallable();
            } catch (\Exception $exception) {
                throw $exception;
            }
        } else {
            return new Response(404);
        }
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function  runController()
    {
        return $this->controllerDispatcher()->dispatch(
            $this, $this->getController(), $this->getControllerMethod()
        );
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function runCallable()
    {
        $callable = $this->parseCallable();

        $dependencies = $this->container->solveMethod($callable['callback'], $callable['parameters'] ?? [])
                                        ->getSolvedParameters();
        return (new Response(http_response_code(), [],
            $callable['callback'](...array_values($dependencies))
        ));
    }

    /**
     * @return null|object
     * @throws \ReflectionException
     */
    public function getController()
    {
        if (!$this->controller) {
            $class = $this->parseControllerName();
            $this->controller = $this->container->solveClass($class);
        }
        return $this->controller;
    }

    /**
     * Parse the callback
     *
     * @return array
     */
    public function parseCallback()
    {
        $callback = $this->router->getCurrentAction()['callback'];
        return explode('@', $callback);
    }

    public function parseCallable()
    {
        return $this->router->getCurrentAction();
    }

    /**
     * Get the controller method used for the route.
     *
     * @return string
     */
    public function getControllerMethod()
    {
        return $this->parseCallback()[1];
    }

    /**
     * Parse the full name of the controller
     *
     * @return string
     */
    public function parseControllerName()
    {
        return $this->controllerNamespace . $this->parseCallback()[0];
    }

    public function controllerDispatcher()
    {
        return new ControllerDispatcher($this->container);
    }

    public function getCurrentRoute()
    {
        return $this->router->getCurrentRoute();
    }

    public function setControllerNamespace(string $controller)
    {
        $this->controllerNamespace = $controller;
    }

    public function getControllerNamespace()
    {
        return $this->controllerNamespace;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function actionIsController($action = null)
    {
        return $this->router->actionIsController($action);
    }

    public function hasRoute()
    {
        return $this->router->getCurrentRoute() !== null;
    }

    public function hasAction()
    {
        return $this->router->getCurrentAction() !== null;
    }
}