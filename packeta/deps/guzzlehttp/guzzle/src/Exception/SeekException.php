<?php

namespace Packetery\GuzzleHttp\Exception;

use Packetery\Psr\Http\Message\StreamInterface;
/**
 * Exception thrown when a seek fails on a stream.
 * @internal
 */
class SeekException extends \RuntimeException implements GuzzleException
{
    private $stream;
    public function __construct(StreamInterface $stream, $pos = 0, $msg = '')
    {
        $this->stream = $stream;
        $msg = $msg ?: 'Could not seek the stream to position ' . $pos;
        parent::__construct($msg);
    }
    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }
}
