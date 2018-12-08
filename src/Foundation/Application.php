<?php

namespace Fenix\Foundation;

use http\Exception\BadMethodCallException;
use Psr\Http\Message\ServerRequestInterface;
use Fenix\Contracts\Container\Container;
use Fenix\Contracts\Support\Renderable;
use Psr\Http\Message\ResponseInterface;
use Fenix\Contracts\Foundation\App;
use Fenix\Routing\RouteCollection;
use Fenix\Http\Message\Response;
use Fenix\Support\Collection;
use Fenix\Routing\Router;
use Fenix\Routing\Route;
use Fenix\Traits\Macro;
use Wzulfikar\WhoopsTrait\RenderExceptionWithWhoops;

/**
 * Application
 *
 * @author Neverson Silva
 */
class Application implements App
{
    use RenderExceptionWithWhoops;

    /**
     * @var Route
     */
    private $route;

    private $debug = true;


    /**
     * Application constructor
     *
     * @param Container $container
     * @param Renderable $render
     * @param string $controller
     * @throws \Fenix\Container\ContainerException
     */
    public function __construct(Route $route, $render = null, Container $container = null, $controller = null)
    {
        $this->setRoute($route);

        if ($container !== null) $this->setContainer($container);
        if ($render !== null) $this->setRenderer($render);
        if ($controller !== null) $this->setControllerNamespace($controller);
    }

    public function __call($name, $args)
    {
        $router = $this->getRouter();
        if (method_exists($router, $name)) {
            return $router->{$name}(...$args);
        }
        throw new \BadMethodCallException(
            sprintf("Method %s::%s doesn't exist.",get_class($this), $name));
    }

    public function setRouter(Router $router)
    {
        $this->route->setRouter($router);
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getRouter()
    {
        return $this->route->getRouter();
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Set the application renderer
     *
     * @param Renderable $render
     * @return void
     * @throws \Fenix\Container\ContainerException
     */
    public function setRenderer(Renderable $render)
    {
        if (!$this->route->container) {
            throw new \Fenix\Container\ContainerException(
                "You need to define a class the implements Fenix\Contracts\Container\Container first."
            );
        }
        $this->route->container->bind('renderer', $render);
    }

    /**
     * Get the application renderer
     *
     * @return Renderable
     */
    public function getRenderer(): Renderable
    {
        if ($this->route->container->has('renderer')) {
            return $this->route->container->get('renderer');
        }

        try {
            return Collection::create($this->route->container->getDependencies())->filter(function($dependencies){
                return $dependencies instanceof Renderable;
            })->first();
        } catch(\Throwable $e) {
            throw new \RuntimeException("No template engine defined.");
        }
    }

    /**
     * Handles a request and produces a response
     *
     * May call other collaborating code to generate the response.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
  
        $this->route->initializeRouteSystem();

        if (!$this->route->hasRoute() && !$this->route->hasAction()) {
            return new Response(404 );
        }
        try {
            return $this->route->bootstrap();
        } catch (\Throwable $ex) {
            /* if ($this->debug && $ex instanceof \Exception) {
                try {
                    return $this->renderExceptionWithWhoops($ex);
                } catch (\Error  $e) {
                    throw $ex;
                }   
            } elseif ($this->debug) {
                throw $ex;
            }
            */
            throw $ex;
            return new Response(500, []);
        }
    }

    /**
     * Set the application controllers namespace
     *
     * @param string $namespace
     * @return void
     */
    public function setControllerNamespace(string $namespace)
    {
        $this->route->setControllerNamespace($namespace);
    }

    /**
     * Get the application controllers namespace
     *
     * @return string
     */
    public function getControllerNamespace(): string
    {
        return $this->route->getControllerNamespace();
    }

    /**
     * Set a container for the application
     *
     * @param Container $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->route->container = $container;
    }

    /**
     * Get the application container
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->route->container;
    }

    public function addDependency($key, $object)
    {
        $this->route->container->bind($key, $object);
    }

    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }
}
