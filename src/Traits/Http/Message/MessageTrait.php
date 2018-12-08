<?php

namespace Fenix\Traits\Http\Message;

use Fenix\Http\Message\Collection\HeaderCollection;
use Psr\Http\Message\StreamInterface;
/**
 * MessageTrait
 *
 * @author Neverson Silva
 */
trait MessageTrait 
{
    /** @var Meltdown\HttpMessage\Collection\HeaderCollection of all registered headers,
     * as original name => array of values */
    private $headers = [];
    /** @var Meltdown\HttpMessage\Collection\HeaderCollection of lowercase
     * header name => original name at registration
     */
    private $headerNames  = [];
    /** @var string */
    private $protocol = '1.1';
    /** @var StreamInterface */
    private $stream;
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }
    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        if ($this->protocol === $version) {
            return $this;
        }
        $newOne = clone $this;
        $newOne->protocol = $version;
        return $newOne;
    }
    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        return $this->headers->toArray();
    }
    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return $this->headerNames->has(strtolower($name));
    }
    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        $name = strtolower($name);
        if(!$this->headerNames->has($name)) {
            return [];
        }
        $name = $this->headerNames->get($name);
        return $this->headers->get($name);
    }
    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }
    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        if (!is_array($value)) $value = [$value];
        $value = $this->trimHeaderValues($value);
        $normalized = strtolower($name);
        $newOne = clone $this;
        if ($this->headerNames->has($normalized)) {
            $newOne->headers->remove(
                $newOne->headerNames->get($normalized)
            );
        }
        $newOne->headerNames->set($normalized, $name);
        $newOne->headers->set($name, $value);
        return $newOne;
    }
    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        if (!is_array($value)) $value = [$value];
        $value = $this->trimHeaderValues($value);
        $normalized = strtolower($name);
        $newOne = clone $this;
        if ($newOne->headerNames->has($normalized)) {
            $name = $this->headerNames->get($normalized);
            $newOne->headers->replace($name, array_merge($this->headers->get($name), $value));
        } else {
            $newOne->headerNames->set($normalized, $name);
            $newOne->headers->set($name, $value);
        }
        return $newOne;
    }
    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        $normalized = strtolower($name);
        if (!$this->headerNames->has($name)) {
            return $this;
        }
        $name = $this->headerNames->get($normalized);
        $newOne = clone $this;
        $this->headers->remove($name);
        $this->headerNames->remove($normalized);
        return $newOne;
    }
    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        if (!$this->stream) {
            $this->stream = StreamHelper::stream_for('');
        }
        return $this->stream;
    }
    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->stream) {
            return $this;
        }
        $newOne = clone $this;
        $newOne->stream = $body;
        return $newOne;
    }
    /**
     * Add a header
     * @param $name
     * @param $value
     */
    public function addHeaderValue($name, $value)
    {
        $this->headers->set($name, $value);
        if ($this->headers->count() > $this->headerNames->count()){
            $this->headerNames->clear();
            $this->headerNames->add($this->headers->keys(true));
        }
    }
    /**
     * Set headers
     * @param array $headers
     */
    protected function setHeaders(array $headers)
    {
        $this->headerNames = new HeaderCollection(); 
        $this->headers = new HeaderCollection();
        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $value = $this->trimHeaderValues($value);
            $normalized = strtolower($header);
            if ($this->headerNames->has($normalized)) {
                $header = $this->headerNames->get($normalized);
                $this->headers->replace($header, array_merge($this->headers->get($header), $value));
            } else {
                $this->headerNames->set($normalized, $header);
                $this->headers->set($header, $value);
            }
        }
    }
    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param string[] $values Header values
     *
     * @return string[] Trimmed header values
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function trimHeaderValues(array $values)
    {
        return array_map(function ($value) {
            return trim($value, " \t");
        }, $values);
    }

}
