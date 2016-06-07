<?php

namespace TimFeid\WebSocket;

use TimFeid\Traits\DynamicGet;

class Handshake
{
    use DynamicGet;

    protected $buffer;
    protected $client;
    protected $shook = false;

    public function __construct(Client $client, $buffer)
    {
        $this->buffer = $buffer;
        $this->client = $client;
        $this->headers = $this->parseHandshake();
        $this->handle();
    }

    public function handle()
    {
        $this->shook = $this->client->writeRaw($this->handshake);
    }

    public function getShook()
    {
        return $this->shook;
    }

    protected function getToken()
    {
        $webSocketKeyHash = sha1($this->headers->key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11');
        $rawToken = null;
        for ($i = 0; $i < 20; ++$i) {
            $rawToken .= chr(hexdec(substr($webSocketKeyHash, $i * 2, 2)));
        }

        return base64_encode($rawToken);
    }

    public function getHandshake()
    {
        $shake = [];
        $shake[] = 'HTTP/1.1 101 Web Socket Protocol Handshake';
        $shake[] = 'Upgrade: WebSocket';
        $shake[] = 'Connection: Upgrade';
        $shake[] = 'Sec-WebSocket-Accept: '.$this->getToken();
        isset($this->headers->protocol) && $shake[] = 'Sec-WebSocket-Protocol: '.$this->headers->protocol;
        $shake[] = 'WebSocket-Origin: '.$this->headers->origin;
        $shake[] = 'WebSocket-Location: ws://'.$this->headers->host.$this->headers->resource;
        $shake = implode("\r\n", $shake)."\r\n\r\n";

        return $shake;
    }

    protected function parseHandshake()
    {
        return (object) [
            'resource' => $this->getResource(),
            'host' => $this->getHost(),
            'origin' => $this->getOrigin(),
            'key' => $this->getKey(),
            'protocol' => $this->getProtocol(),
            'extensions' => $this->getExtensions(),
        ];
    }

    protected function getResource()
    {
        if (preg_match('/GET (.*) HTTP/', $this->buffer, $match)) {
            return $match[1];
        }

        return;
    }

    protected function getHost()
    {
        if (preg_match('/Host: (.*)\r\n+/', $this->buffer, $match)) {
            return $match[1];
        }

        return;
    }

    protected function getOrigin()
    {
        if (preg_match('/Origin: (.*)\r\n+/', $this->buffer, $match)) {
            return $match[1];
        }

        return;
    }

    protected function getKey()
    {
        if (preg_match('/Sec-WebSocket-Key: (.*)\r\n+/', $this->buffer, $match)) {
            return $match[1];
        }

        return;
    }

    protected function getProtocol()
    {
        if (preg_match('/Sec-WebSocket-Protocol: (.*)\r\n+/', $this->buffer, $match)) {
            return $match[1];
        }

        return;
    }

    protected function getExtensions()
    {
        if (preg_match('/Sec-WebSocket-Extensions: (.*)\r\n+/', $this->buffer, $match)) {
            return $match[1];
        }

        return;
    }
}
