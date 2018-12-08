<?php

namespace Fenix\Routing;

use Fenix\Contracts\Routing\RouteCollection as RouteContract;
use Fenix\Support\Collection;

final class RouteCollection implements RouteContract
{

    private $routes = [];


    public function add($method, $uri, $call)
    {
        if (!isset($this->routes[$method])) {
            $this->routes[$method] =  new Collection();
        }
        if (!$this->routes[$method]->hasKey($uri)) {
            $this->routes[$method]->add($uri, $call);
        }
    }

    public function filter($method)
    {
        if (!isset($this->routes[$method])) {
            $this->routes[$method] =  new Collection();
        }
        return $this->routes[$method];
    }

    public function all()
    {
        return $this->routes;
    }
}