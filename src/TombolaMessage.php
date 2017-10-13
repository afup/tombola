<?php

namespace Afup\Tombola;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class TombolaMessage implements MessageComponentInterface
{
    /**
     * @var \SplObjectStorage
     */
    protected $clients;

    /**
     * @var \SplObjectStorage
     */
    protected $admins;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        $this->admins = new \SplObjectStorage();
    }

    function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $message = json_decode($msg, true);

        $admin = (bool)$message['admin'];

        if (isset($message['type']) && $message['type'] === 'connection' && $admin === true) {
            echo sprintf("New admin %s connected\n", $message['nickname']);
            $this->admins->attach($from);
            $this->clients->detach($from);

            return;
        }

        // We send the messages to the clients XOR to the admins, regarding the source of the message
        $fanOut = $this->clients;

        if ($admin === false) {
            $fanOut = $this->admins;
        }

        foreach ($fanOut as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each other clients connected
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages

        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }
        if ($this->admins->contains($conn)) {
            $this->admins->detach($conn);
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
