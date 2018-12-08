<?php

namespace Fenix\Routing;

use Fenix\Contracts\Routing\Request as RequestContract;
use Fenix\Http\Message\ServerRequest;

/**
 * Request class
 *
 * Get the request from user to the server
 *
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 * @package Fenix
 * @subpackage Routing
 * @license MIT
 */
class Request implements RequestContract
{
    protected $server;

    public function __construct()
    {
        $this->server = ServerRequest::createFromGlobals();
    }
    /**
     *
     * @return ServerRequest
     */
    public function getServerRequest()
    {
        return $this->server;
    }
    /**
     * Get an instance of a object that implements UriInterface
     *
     * With the method getPath() from the returned object it's possible
     * retrive current URI
     *
     * @return \Fenix\Http\Message\Uri
     */
    public function url()
    {
        return $this->server->getUriFromGlobals();
    }

    /**
     * Get all data from get and post requisitions
     *
     * @return array Data from $_GET or $_POST request.
     */
    public function all(): array
    {
        return $this->sanitize(
            $this->getServerParams()
        );
    }

    public function hasInput($input)
    {
        return !empty($this->input($input));
    }
    /**
     * Get an input request
     *
     * @param string $input
     * @return mixed
     */
    public function input($input)
    {
        $params = $this->sanitize($this->getServerParams());
        return $params[$input] ?? null;
    }

    /**
     * This is an alias to get function
     *
     * Get payload requests
     *
     * @param mixed $input
     * @return mixed
     */
    public function json($input)
    {
        return $this->get($input);
    }
    /**
     * Get payload request from ajax requests
     *
     * @param $input
     * @return mixed
     */
    public function get($input)
    {
        return Payload::get(
            $this->sanitize($input)
        );
    }

    /**
     * Get all items in a payload request
     *
     * @return mixed
     */
    public function getAll()
    {
        return $this->sanitize(
            Payload::all()
        );
    }

    /**
     * Get params base on type of request
     *
     * @return array Parameters from $_GET or $_POST request.
     */
    public function getServerParams()
    {
        if ($this->server->isEmptyQueryParams()) {
            return $this->server->getParsedBody();
        }
        return $this->server->getQueryParams();
    }

    /**
     * Escape html special values and remove with spaces
     *
     * @param mixed $data
     * @return string|array
     */
    protected function escapeHtmlChars($data)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => &$value) {
                $value = htmlspecialchars(trim($value));
            }
        } else {
            $data = htmlspecialchars(trim($data));
        }
        return $data;
    }

    /**
     * Sanitize user inputs
     * @param mixed $data User Input
     * @return mixed|string
     */
    public function sanitize($data)
    {
        if (is_iterable($data)) {
            return $this->filterMultiple($data);
        }
        return $this->filter($data);
    }

    private function filter($value)
    {
        if (is_iterable($value) ){
            return $this->filterMultiple($value);
        }
        return trim(filter_var($value, FILTER_SANITIZE_STRING));
    }

    private function filterMultiple(iterable $data)
    {
        return array_map([$this, 'filter'], $data);
    }
}