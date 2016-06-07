<?php

namespace TimFeid\WebSocket;

use InvalidArgumentException;
use TimFeid\Traits\DynamicGet;

class Client
{
    use DynamicGet;

    const STATE_HANDSHAKE = 1;
    const STATE_CONNECTED = 2;

    protected $resource;
    protected $state = self::STATE_HANDSHAKE;
    protected $clients;

    public function __construct(Clients $clients, $resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Argument 1 needs to be a resource');
        }

        $this->clients = $clients;
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function read()
    {
        $buffer = fread($this->resource, 2048);

        if ($this->state === self::STATE_HANDSHAKE) {
            return $this->handshake($buffer);
        }

        return $this->input($buffer);
    }

    public function handshake($buffer)
    {
        $handshake = new Handshake($this, $buffer);

        if ($handshake->shook) {
            $this->state = self::STATE_CONNECTED;
        }
    }

    protected function input($buffer)
    {
        $input = new Input($this, $buffer);

        return $this->clients
            ->server
            ->handler
            ->input($input);
    }

    public function mask()
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }

        return $header.$text;
    }

    public function write($output, $raw = false)
    {
        if (!$raw) {
            $output = $this->mask($output);
        }

        return fwrite($this->resource, $output, strlen($output));
    }

    public function writeRaw($output)
    {
        return $this->write($output, true);
    }
}
