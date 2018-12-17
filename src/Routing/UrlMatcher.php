<?php

namespace Fenix\Routing;

use Fenix\Contracts\Routing\UrlParameter;
use Fenix\Support\Collection;
use Fenix\Support\Strin;
use Fenix\Support\Arra;

final class UrlMatcher implements UrlParameter
{
    /**
     * @var Collection
     */
    private $routes;
    /**
     * @var Collection
     */
    private $server;
    /**
     * UrlMatcher constructor.
     * @param $routes
     * @param array $server
     */
    public function __construct($routes, array $server)
    {
        $this->routes = $routes;
        $this->server = (new Collection($server))->toLower('keys');
    }
    /**
     * Match parameters
     *
     * @param $url
     * @return array
     */
    public function matchParameters($url)
    {
        $url = Strin::withDefault($url, '', '/');

        $rota = $this->routes->filter(function($item, $key) use ($url){
            
            if (Strin::compare($key, '/')) {
                return $url->equals($key);
            }
            return Strin::create($key)
                        ->trim('/')
                        ->equals($url);
        });

        if (!is_array($rota)) {
            if (!$rota->isEmpty()) {
                return [
                    'callback' => $rota->values()[0],
                    'route' => $rota->keys()[0],
                ];
            }
        }
        $rotaComParametros = $this->routes->filter(function($item, $key) use($url){
            return Strin::create($key)->position('{');
        });

        $callback = null;
        $parameters = false;

        foreach ($rotaComParametros as $rota => $action ) {
            $parameters = $this->getParametersFromUrl($rota, $url);
            if ($parameters != false) {
                $callback = $action;
                $route = $rota;
                break;
            }
        }
        if ($callback == null && $parameters == false) return null;
        return compact('callback', 'route', 'parameters');
    }
    /**
     * Obtém parametros a partir da URL
     *
     * Pega os parametros a partir da URL registrada na rota
     *
     * @param string $url
     * @param string $subject
     * @return array|bool
     */
    public function getParametersFromUrl($url, $subject)
    {
        $url = Strin::withDefault($url, '/', $url)->trim('/');

        $subject = Strin::withDefault($subject, '/', $subject)->trim('/');

        $variables = $url->matchAll('/\{([^\}]*)\}/');

        $regex = $url->replaceNew('/', '\/');

        $variables->get(1)->map(function($variable, $key) use($regex, $variables) {

            $replacement = (string) Strin::explode(':', $variable)->get(1, '([a-zA-Z0-9\-\_\ ]+)');

            if ($variables->exists($key)) {

                $variable = $variables[$key];

                if ($variable instanceof Arra) {
                    $variable = $variable->getItems();
                }
                $regex->replace($variable, $replacement);
            }
        });

        $regex->replace('/{([a-zA-Z]+)}/', '([a-zA-Z0-9+])');

        $result = $subject->match($regex->wrap(['/^', '$/']), null, true);

        if ($result) {
            array_shift($result);
            $parameters = [];
            if (count($variables[1]) == count($result)) {
                for ($i = 0; $i < count($result); $i++) {
                    $parameters[$variables[1][$i]] = $result[$i];
                }
            }
            return $parameters;
        }
        return false;
    }
}