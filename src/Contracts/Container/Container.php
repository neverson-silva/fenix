<?php
namespace Fenix\Contracts\Container;

use Psr\Container\ContainerInterface as ContainerContract;
/**
 *
 * @author Neverson Silva
 */
interface Container extends ContainerContract
{
     /**
     * Bind and object to container
     *
     * @param string $key
     * @param mixed $object
     * @return void
     */
    public function bind(string $key = '', $object, $params = []);
    
    /**
     * Return all objetos within the container
     *
     * @return array
     */
    public function getDependencies() : array;
    
    /**
     * Resolve methods/functions dependencies
     *
     * @param $method
     * @param array $dependencies
     * @return void
     */
    public function solveMethod($method, $dependencies = []);
    
    /**
     * Resolve class dependencies
     *
     * @param  $class
     * @param array $dependencies
     * @return void
     */
    public function solveClass($class, $dependencies = []);
    
    /**
     * Forget a depencie in the container
     *
     * @param  $id
     * @return void
     */
    public function forget($id);
    
    /**
     * Resolve paramaters dependencies
     *
     * @param array $dependencies
     * @param array $paramaters
     * @return Response
     */
    public function respond($action, array $paramaters);
}
