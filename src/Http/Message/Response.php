<?php

namespace Fenix\Http\Message;

use Fenix\Http\Message\Collection\HttpCollection;
use Wzulfikar\WhoopsTrait\RenderExceptionWithWhoops;
use Fenix\Http\Message\Collection\Collection;
use Fenix\Traits\Http\Message\MessageTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Response implements ResponseInterface
{
    use MessageTrait;

    /** @var HttpCollection Map of standard HTTP status code/reason phrases */
    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];


    /** @var string */
    private $reasonPhrase = '';

    /** @var int */
    private $statusCode = 200;
    
    private $corpo;


    public function __construct($status = 200, array $headers = [], $body = null,  $version = '1.1',  $reason = null)
    {
        if (!self::$phrases instanceof Collection) {
            self::$phrases = new HttpCollection(self::$phrases);
        }
        $this->statusCode = (int) $status;

       
        if (is_string($body)) {
             if ($body !== '' && $body !== null) {
                $this->stream = StreamHelper::stream_for($body);
            }                
        }

        $this->setHeaders($headers);
        if ($reason == '' && self::$phrases->has($this->statusCode)) {
            $this->reasonPhrase = self::$phrases->get($this->statusCode);
        } else {
            $this->reasonPhrase = (string) $reason;
        }

        $this->protocol = $version;
        $this->setCorpo($body);
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return (int) $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $newOne = clone $this;
        $newOne->statusCode = (int) $code;

        if (!self::$phrases instanceof Collection) {
            self::$phrases = new HttpCollection(self::$phrases);
        }
        if ($reasonPhrase = '' && self::$phrases->has($newOne->statusCode)) {
            $reasonPhrase = self::$phrases->get($newOne->statusCode);
        }
        $newOne->reasonPhrase = $reasonPhrase;

        return $newOne;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return (string) $this->reasonPhrase;
    }
    
    public function setCorpo($corpo)
    {
        $this->corpo = $corpo;
        return $this;
    }
    
    public function getCorpo()
    {
        return $this->corpo;
    }

    /**
     * Type of response to dispatch to application
     *
     * @param  $call
     * @return mixed
     */
    public function respond($call = '')
    {

        if ($call == '') {
            $call = $this->getCorpo();
        }

        if ($this->statusCode != 200 && $call == null) {
           $this->header($code = $this->getStatusCode());
        } elseif ($call !== null) {
            $this->header($code = $this->getStatusCode());
        }
        if (is_string($call)){
            echo $call;
        } elseif(is_array($call)) {
            return var_dump($call);
        } elseif(is_object($call)) {
            if (!is_null($call)) {
                return var_dump($call);
            }
            return $call;
        }
        return $this;
    }

    public function header($code = null)
    {
        ob_start();
        $code = $code !== null ? $code : $this->getStatusCode();

        if (is_int($code)) {
            $phrase = static::$phrases->get($code);

            $message = sprintf('HTTP/1.0 %s %s', $code, $this->getReasonPhrase());
            header($message);
        } else {
            header($code);
        }
        ob_clean();

        return $this;

    }
}