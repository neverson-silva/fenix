<?php

namespace Fenix\Traits;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Fenix\Routing\RouteCollection;
use Fenix\Container\Container;
use Fenix\Routing\Router;
use Fenix\Routing\Route;

trait Macro
{

    public function setRouteCollection(RouteCollection $routeCollection = null)
    {
        if (property_exists($this, 'routeCollection')) {
            $this->routeCollection = $routeCollection;
        }
        if (property_exists($this, 'routes')) {
            $this->routes = $routeCollection;
        }
    }

    public function getRouteCollection() : RouteCollection
    {
        return $this->routeCollection ?? $this->routes ?? null;
    }

    public function setContainer(Container $container = null)
    {
        if (property_exists($this, 'container')) {
            $this->container = $container;
        }
    }
    public function getContainer() : Container
    {
        return $this->container ?? null;
    }

    public function setRoute(Route $route = null)
    {
        if (property_exists($this, 'route')) {
            $this->route = $route;
        }
    }

    public function getRoute() : Route
    {
        return $this->route ?? null;
    }

    public function setRouter(Router $router = null)
    {
        if (property_exists($this, 'router')) {
            $this->router = $router;
        }
    }

    public function getRouter() : Router
    {
        return $this->router ?? null;
    }

    public function setServerRequest(ServerRequestInterface $serverRequest)
    {
        if (property_exists($this, 'serverRequest')) {
            $this->serverRequest = $serverRequest;
        }
    }

    public function getServerRequest() : ServerRequestInterface
    {
        return $this->serverRequest ?? null;
    }

    public function setUri(UriInterface $uri)
    {
        if (property_exists($this, 'uri')) {
            $this->uri = $uri;
        }
    }

    public function getUri() : UriInterface
    {
        return $this->uri ?? null;
    }

}