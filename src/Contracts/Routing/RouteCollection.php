<?php
namespace Fenix\Contracts\Routing;


interface RouteCollection
{
    public function add($method, $uri, $call);

    public function filter($method);

    public function all();
}