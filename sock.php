<?php

use TimFeid\WebSocket\Input;
use TimFeid\WebSocket\Server;
use TimFeid\WebSocket\Handler;

include 'vendor/autoload.php';

class MyHandler extends Handler
{
    public function input(Input $input)
    {
        echo "< $input\n";
    }
}

$server = new Server('ws://0.0.0.0:27002', new MyHandler());
$server->run();
