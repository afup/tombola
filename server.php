<?php
use Ratchet\Server\IoServer;
use Afup\Tombola\TombolaMessage;

require dirname(__FILE__) . '/vendor/autoload.php';

$server = IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new TombolaMessage()
        )
    ),
    8090
);

$server->run();
