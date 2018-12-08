<?php

namespace Fenix\Contracts\Foundation;

use Psr\Http\Message\ServerRequestInterface;
use Fenix\Contracts\Support\Renderable;
use Fenix\Contracts\Container\Container;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for the application instance
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */
interface App 
{
    /**
     * Set the application renderer
     *
     * @param Renderable $render
     * @return void
     */
    public function setRenderer(Renderable $render);

    /**
     * Get the application renderer
     *
     * @return Renderable
     */
    public function getRenderer() : Renderable;

    /**
     * Set a container for the application
     *
     * @param Container $container
     * @return void
     */
    public function setContainer(Container $container);

    /**
     * Get the application container
     *
     * @return Container
     */
    public function getContainer() : Container;

    /**
     * Handles a request and produces a response
     *
     * May call other collaborating code to generate the response.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface;

    /**
     * Set the application controllers namespace
     *
     * @param string $namespace
     * @return void
     */
    public function setControllerNamespace(string $namespace);

    /**
     * Get the application controllers namespace
     *
     * @return string
     */
    public function getControllerNamespace() : string;

}
