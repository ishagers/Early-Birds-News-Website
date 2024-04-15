<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userLookup = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $session = $conn->WebSocket->request->getCookies()['PHPSESSID'];
        $userId = $this->verifySession($session);
        if ($userId) {
            $this->clients->attach($conn);
            $this->userLookup[$userId] = $conn;
            echo "New connection! ({$conn->resourceId}) by user {$userId}\n";
        } else {
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        if ($data->type == 'message' && isset($this->userLookup[$data->targetUserId])) {
            $targetConn = $this->userLookup[$data->targetUserId];
            $targetConn->send($data->message);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $userId = array_search($conn, $this->userLookup);
        unset($this->userLookup[$userId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function verifySession($sessionId) {
        // Verify session ID and return user ID if valid
        return $_SESSION[$sessionId]['user_id'] ?? false;
    }
}


