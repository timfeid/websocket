<?php

namespace TimFeid\WebSocket;

abstract class Handler
{
    protected $server;
    final public function setServer(Server $server)
    {
        $this->server = $server;
    }

    abstract public function input(Input $input);

    public function connected(Client $client)
    {
    }

    public function closed(Client $client)
    {
    }
}
