<?php

namespace TimFeid\WebSocket;

use InvalidArgumentException;
use TimFeid\Traits\DynamicGet;

class Clients
{
    use DynamicGet;

    protected $server;
    protected $clients = [];
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function add($client)
    {
        $this->clients[] = new Client($this, $client);
    }

    public function remove(Client $client)
    {
        if (($index = $this->findIndexOf($client)) !== false) {
            unset($this->clients[$this->findIndexOf($client)]);
        }
    }

    protected function findIndexOf(Client $client)
    {
        return array_search($client, $this->clients);
    }

    public function loop()
    {
        $read = array_merge($this->resources, [$this->server->resource]);
        $write = null;
        $except = null;

        if (stream_select($read, $write, $except, 0)) {
            foreach ($read as $resource) {
                $this->handleResource($resource);
            }
        }
    }

    protected function handleResource($resource)
    {
        if (!is_resource($resource)) {
            return;
        }

        if ($resource == $this->server->resource) {
            return $this->accept();
        }

        return $this->read($resource);
    }

    protected function accept()
    {
        if ($resource = stream_socket_accept($this->server->resource)) {
            $this->add($resource);
        }
    }

    protected function read($resource)
    {
        if ($resource instanceof Client) {
            return $resource->read();
        }

        if (($client = $this->findClientByResource($resource)) !== false) {
            return $client->read();
        }
    }

    protected function findClientByResource($resource)
    {
        if (is_resource($resource)) {
            foreach ($this->clients as $client) {
                if ($client->resource === $resource) {
                    return $client;
                }
            }

            return false;
        }

        throw new InvalidArgumentException('Arugment 1 needs to be a resource or '.Client::class);
    }

    public function getResources()
    {
        $resources = [];
        foreach ($this->clients as $client) {
            $resources[] = $client->resource;
        }

        return $resources;
    }

    public function getServer()
    {
        return $this->server;
    }
}
