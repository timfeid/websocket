<?php

namespace TimFeid\WebSocket;

use TimFeid\Traits\DynamicGet;

class Server
{
    use DynamicGet;

    protected $clients;
    protected $handler;
    protected $resource;
    protected $destination;
    protected $secure;
    protected $context;

    public function __construct($destination, Handler $handler)
    {
        $this->context = stream_context_create();
        $this->handler = $handler;
        $this->handler->setServer($this);
        $this->clients = new Clients($this);
        $this->parseDestination($destination);
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    protected function parseDestination($destination)
    {
        if (preg_match('/^ws(s)?:\/\/(.*)$/', $destination, $match)) {
            $this->destination = $match[2];
            $this->secure = (bool) $match[1];

            return;
        }

        throw new Exception('URL is not a websocket url, please use ws:// or wss://');
    }

    public function run()
    {
        $this->bindSocket();
        while (1) {
            $this->clients->loop();
            usleep(100);
        }
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    protected function bindSocket()
    {
        $err = $errno = 0;

        $this->resource = stream_socket_server($this->destination, $err, $errno, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $this->context);
    }
}
