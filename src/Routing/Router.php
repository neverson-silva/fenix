<?php

namespace Fenix\Routing;

use Fenix\Contracts\Routing\Router as RouterContract;
use Fenix\Contracts\Routing\RouteCollection;
use Fenix\Http\Message\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Fenix\Traits\Macro;
use Closure;

class Router implements RouterContract
{
    use Macro;

    private $uri;
    private $serverRequest;
    private $routes;
    private $currentAction;
    private $currentRoute;
    private $prefix;
    

    /**
     * Constructor of Router
     * @param ServerRequest $serverRequest A request object
     * @param UriInterface $uri A uri Object
     * @param RouteCollection $routes The routes
     */
    public function __construct(ServerRequest $serverRequest = null, UriInterface $uri = null, RouteCollection $routes = null)
    {
        if ($serverRequest) {
            $this->setServerRequest($serverRequest);
            if (!$uri) {
                $this->setUri($this->serverRequest->getUriFromGlobals());
            }
        }
        if ($uri) {
            $this->setUri($uri);
        }

        $this->setRouteCollection($routes ?? new \Fenix\Routing\RouteCollection());

    }

    /**
     * Define a ação atual a sendo executada
     *
     * @param array $currentAction
     */
    public function setCurrentAction($currentAction)
    {
        if (isset($currentAction['route'])) unset($currentAction['route']);
        $this->currentAction = $currentAction;
    }

    /**
     * Define a rota atual com base na URI que está sendo visitada
     *
     * @param string $currentRoute
     */
    public function setCurrentRoute($currentRoute)
    {
        $this->currentRoute = $currentRoute;
    }

    /**
     * Recupera a rota atual
     *
     * @return string
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
    /**
     * Recuperar a ação atual sendo executada
     *
     * @return array
     */
    public function getCurrentAction($offset = null)
    {
        return is_null($offset) ? $this->currentAction : $this->currentAction[$offset];
    }

    public function addRoute($method, $uri, $call)
    {
        if ($this->prefix !== null) {
            $uri = $this->prefix . '/' . ltrim($uri, '/');
        }
        return $this->routes->add($method, $uri, $call);
    }

    public function get($uri, $call)
    {

        return $this->addRoute('GET', $uri, $call);
    }
    public function post($uri, $call)
    {
        return $this->addRoute('POST', $uri, $call);
    }
    public function delete($uri, $call)
    {
        return $this->addRoute('DELETE', $uri, $call);
    }
    public function put($uri, $call)
    {
        return $this->addRoute('PUT', $uri, $call);
    }
    public function options($uri, $call)
    {
        return $this->addRoute('OPTIONS', $uri, $call);
    }
    public function header($uri, $call)
    {
        return $this->addRoute('HEADER', $uri, $call);
    }

    public function run()
    {
        if (!$this->serverRequest) {
            throw new \Exception("You need to setup a request object.");
        }
        $server = $this->serverRequest->getServerParams();

        $method = $this->resolveMethod(new Request, $this->serverRequest);

        $url = new UrlMatcher($this->filter($method), $server);

        $action = $url->matchParameters($this->uri->getTrimedPath());

        if (!empty($action)) {
            $this->setCurrentAction($action);
            $this->setCurrentRoute($action['route']);
        } else {
            $this->clearRouteAction();
        }
        return $this;

    }

    public function clearRouteAction()
    {
        $this->currentRoute = null;
        $this->currentAction = null;
    }

    public function resolveMethod(Request $request, ServerRequestInterface $methods)
    {
        if ($request->hasInput('__method')) {
            return $request->input('__method');
        }
        return $methods->getServerParams()['REQUEST_METHOD'];
    }

    /**
     * Checa se ação é controller
     *
     * Verifica se a ação corrente sendo executada é um controller
     *
     * @param $action Ação sendo executada, se for uma string é um controller
     * @return bool Caso seja controller retorna true, caso contrário false
     */
    public function actionIsController($action = null)
    {
        if ($action == null) {
            $action = $this->getCurrentAction('callback');
        }
        if ($action instanceof Closure) {
            return false;
        }
        return true;
    }

    /**
     *
     * Filtra as rotas registradas com base no método da requisição sendo enviada para o servidor
     *
     * @param string $method Método Http
     * @return mixed
     */
    public function filter($method)
    {
        return $this->routes->filter($method);
    }


    /**
     * URI atual
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->uri->getTrimedPath();
    }

        /**
     * Prefix a group of routes
     *
     * @param string $prefix
     * @return $this
     */
    public function prefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }
    /**
     * Group some routes
     *
     * @param callable $callable
     * @return $this
     */
    public function group(Closure $callable)
    {
        call_user_func($callable);
        $this->prefix = '';
        return $this;
    }

    /**
     * Resource restfull endpoints
     *
     * @param string $uri
     * @param string $controller
     * @return void
     */
    public function resource(string $uri, string $controller)
    {
        $this->get($uri, "$controller@index");
        $this->get("$uri/show/{id}", "$controller@show");
        $this->get("$uri/create", "$controller@create");
        $this->post($uri, "$controller@store");
        $this->get("$uri/edit/{id}", "$controller@edit");
        $this->post("$uri/update/{id}", "$controller@update");
        $this->put("$uri/update/{id}", "$controller@update");
        $this->post("$uri/delete/{id}", "$controller@delete");
    }

}