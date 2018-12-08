<?php
namespace Fenix\Contracts\Routing;


interface Router
{
    public function get($uri, $call);
    public function post($uri, $call);
    public function delete($uri, $call);
    public function put($uri, $call);
    public function options($uri, $call);
    public function header($uri, $call);
    public function addRoute($method, $uri, $call);
    public function run();
    public function actionIsController($action);

}