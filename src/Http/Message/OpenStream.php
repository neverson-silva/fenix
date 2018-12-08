<?php

namespace Fenix\Http\Message;

use Fenix\Traits\Http\Message\StreamDecorator;
use Psr\Http\Message\StreamInterface;
/**
 * OpenStream
 *
 * @author Neverson Silva
 */
class OpenStream implements StreamInterface
{
    use StreamDecorator;
    
    /** @var string File to open */
    private $filename;
    
    /** @var string $mode */
    private $mode;
    
    /**
     * @param string $filename File to lazily open
     * @param string $mode     fopen mode to use when opening the stream
     */
    public function __construct($filename, $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
    }
    
    /**
     * Creates the underlying stream lazily when required.
     *
     * @return StreamInterface
     */
    protected function createStream()
    {
        return StreamHelper::stream_for(
            StreamHelper::try_open($this->filename, $this->mode)
        );
    }
}
