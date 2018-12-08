<?php

namespace Fenix\Routing;

use Fenix\Contracts\Routing\UrlParameter;
use Fenix\Support\Collection;

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
        $url = $url == '' ? '/' : $url;
        $rota = $this->routes->filter(function($item, $key) use ($url){
            if ($key === '/') {
                return $key == $url;
            }
            return trim($key, '/') === $url;
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
            return strpos($key, '{');
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
    public function getParametersFromUrl(string $url, string $subject)
    {
        $url = $url == '/' ? $url : trim($url, '/');
        $subject = $subject == '/' ? $subject : trim($subject, '/');
        preg_match_all('/\{([^\}]*)\}/', $url, $variables);
        $regex = str_replace('/', '\/', $url);
        foreach ($variables[1] as $key => $variable) {
            $as = explode(':', $variable);
            $replacement = $as[1] ?? '([a-zA-Z0-9\-\_\ ]+)';
            if (isset($variables[$key])) {
                $regex = str_replace($variables[$key], $replacement, $regex);
            }
        }
        $regex = preg_replace('/{([a-zA-Z]+)}/', '([a-zA-Z0-9+])', $regex);
        $result = preg_match('/^' . $regex . '$/', $subject, $params);
        if ($result) {
            array_shift($params);
            $parameters = [];
            if (count($variables[1]) == count($params)) {
                for ($i = 0; $i < count($params); $i++) {
                    $parameters[$variables[1][$i]] = $params[$i];
                }
            }
            return $parameters;
        }
        return false;
    }
}