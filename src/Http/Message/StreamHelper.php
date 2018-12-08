<?php

namespace Fenix\Http\Message;

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use RuntimeException;
use Iterator;

class StreamHelper
{

    /**
     * StreamHelper constructor.
     * Cannot be instantiate.
     */
    private function __construct()
    {
    }

    /**
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * @param resource|string|null|int|float|bool|StreamContract|callable $resource Entity body data
     * @param array                                                        $options  Additional options
     * @see stream_for() at guzzlehttp/psr7/src/functions
     * @return StreamInterface
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    public static function stream_for($resource = '', array $options = [])
    {
        if (is_scalar($resource)) {
            $stream = fopen('php://temp', 'r+');
            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }
            return new Stream($stream, $options);
        }
        switch(gettype($resource)) {
            case 'resource':
                return new Stream($resource, $options);
                break;
            case 'object':
                if ($resource instanceof StreamInterface) {
                    return $resource;
                } elseif ($resource instanceof Iterator) {
                    return new ThrobStream(function() use ($resource) {
                        if (!$resource->valid()) {
                            return false;
                        }
                        $result = $resource->current();
                        $resource->next();
                        return $result;
                    }, $options);
                } elseif(method_exists($resource, '__toString')) {
                    return self::stream_for((string) $resource, $options);
                }
                break;
            case 'NULL':
                return new Stream(fopen('php://temp', 'r+'), $options);
        }
        if (is_callable($resource)) {
            return new ThrobStream($resource, $options);
        }
        throw new InvalidArgumentException('Invalid resource type: ' . gettype($resource));
    }

    /**
     * Copy the contents of a stream into a string until the given number of
     * bytes have been read.
     *
     * @param StreamInterface $stream Stream to read
     * @param int             $maxLen Maximum number of bytes to read. Pass -1
     *                                to read the entire stream.
     *  @see stream_for() at guzzlehttp/psr7/src/functions
     * @return string
     * @throws \RuntimeException on error.
     */
    public static function copy_to_string(StreamInterface $stream, $maxLen = -1)
    {
        $buffer = '';

        if ($maxLen === -1) {
            while (!$stream->eof()) {
                $buf = $stream->read(1048576);
                // Using a loose equality here to match on '' and false.
                if ($buf == null) {
                    break;
                }
                $buffer .= $buf;
            }
            return $buffer;
        }

        $len = 0;
        while (!$stream->eof() && $len < $maxLen) {
            $buf = $stream->read($maxLen - $len);
            // Using a loose equality here to match on '' and false.
            if ($buf == null) {
                break;
            }
            $buffer .= $buf;
            $len = strlen($buffer);
        }

        return $buffer;
    }

    /**
     * Safely opens a PHP stream resource using a filename.
     *
     * When fopen fails, PHP normally raises a warning. This function adds an
     * error handler that checks for errors and throws an exception instead.
     *
     * @param string $filename File to open
     * @param string $mode     Mode used to open the file
     * @see stream_for() at guzzlehttp/psr7/src/functions
     * @return resource
     * @throws \RuntimeException if the file cannot be opened
     */
    public static function try_open($filename, $mode)
    {
        $ex = null;
        set_error_handler(function () use ($filename, $mode, &$ex) {
            $ex = new \RuntimeException(sprintf(
                'Unable to open %s using mode %s: %s',
                $filename,
                $mode,
                func_get_args()[1]
            ));
        });

        $handle = fopen($filename, $mode);
        restore_error_handler();

        if ($ex) {
            /** @var $ex \RuntimeException */
            throw $ex;
        }
        return $handle;
    }

    /**
    * Copy the contents of a stream into another stream until the given number
    * of bytes have been read.
    *
    * @param StreamInterface $source Stream to read from
    * @param StreamInterface $dest   Stream to write to
    * @param int             $maxLen Maximum number of bytes to read. Pass -1
    *                                to read the entire stream.
    * @see stream_for() at guzzlehttp/psr7/src/functions
    * @throws \RuntimeException on error.
    */
    public static function copy_to_stream(StreamInterface $source, StreamInterface $dest, $maxLen = -1)
    {
       $bufferSize = 8192;
   
       if ($maxLen === -1) {
           while (!$source->eof()) {
               if (!$dest->write($source->read($bufferSize))) {
                   break;
               }
           }
       } else {
           $remaining = $maxLen;
           while ($remaining > 0 && !$source->eof()) {
               $buf = $source->read(min($bufferSize, $remaining));
               $len = strlen($buf);
               if (!$len) {
                   break;
               }
               $remaining -= $len;
               $dest->write($buf);
           }
       }
   }

}