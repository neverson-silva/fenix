<?php

namespace Fenix\Http\Message;

use Fenix\Traits\Http\Message\MessageTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

class Request implements RequestInterface
{
    use MessageTrait;
    
    /** @var string */
    private $method;

    /** @var null|string */
    private $requestTarget;

    /** @var UriInterface */
    private $uri;


    /**
     * Request constructor.
     * @param string $method     HTTP method
     * @param $uri               URI
     * @param array $headers     headers
     * @param string $body         body of content
     * @param string $version    Protocol version
     */
    public function __construct($method, $uri, array $headers = [], $body = null,  $version = '1.1')
    {
        $this->method = $method;

        if (!$uri instanceof UriInterface) {
            $this->uri = new Uri($uri);
        } else {
            $this->uri = $uri;
        }
        $this->method = \strtoupper($method);
        $this->setHeaders($headers);
        $this->protocol = $version;

        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }
        if ($body !== '' && $body !== null){
            $this->stream = StreamHelper::stream_for($body);
        }
    }


    /**
     * Create an new Request instance from global variables
     * @return static
     */
    public static function create()
    {
        return new static(
            $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']
        );
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (!is_null($this->requestTarget)) {
            return $this->requestTarget;
        }
        $target = $this->uri->getPath();
        if ($target == ''){
            $target = '/';
        }
        if(!empty($this->uri->getQuery())){
            $target .= '?' . $this->uri->getQuery();
        }
        return $target;
    }


    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }

        $newOne = clone $this;
        $newOne->requestTarget = $requestTarget;
        return $newOne;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return static
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $newOne = clone $this;
        $newOne->method = \strtoupper($this->method);
        return $newOne;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($this->uri === $uri) {
            return $this;
        }
        $newOne = clone $this;
        $newOne->uri = $uri;

        if (!$preserveHost) {
            $newOne->updateHostFromUri();
        }
        return $newOne;
    }

    /**
     * @see GuzzleHttp\Psr7\Request
     *
     * @return void
     */
    public function updateHostFromUri()
    {
        $host = $this->uri->getHost();

        if ($host == '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }
        
        if ($this->headerNames->has('host')) {
            $header = $this->headerNames->get('host');
        } else {
            $header = 'Host';
            $this->headerNames->set('host', 'Host');
        }
        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = [$header => [$host]] + $this->headers->toArray();
    }
}