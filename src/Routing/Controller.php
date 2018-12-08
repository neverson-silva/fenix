<?php

namespace Fenix\Routing;

use Fenix\Http\Message\Response;
use Fenix\Container\Container;

class Controller
{

    /**
     * Render a view
     */
    public function render($view, $params = [])
    {
        $container = Container::getContainer();
        if ($container->has('renderer')) {
            $renderer = $container->get('renderer');
            return $renderer->render($view, $params);
        }
        return null;
    }

    /**
     *
     * Call the controller action
     *
     * @param string $action
     * @param array $params
     * @return Response
     */
    public function callAction($action, $params)
    {
        if (!is_array($params)) {
            throw new \InvalidArgumentException(sprintf('Params should be an array %s passed.', gettype($params)));
        }
        return new Response(
            http_response_code(), [],
            call_user_func_array([$this, $action], $params)
        );
    }

}