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
        parse_str($conn->WebSocket->request->getQuery(), $params);
        $token = $params['token'] ?? null;
        $userId = $this->verifySession($token);

        if ($userId) {
            $this->clients->attach($conn);
            $this->userLookup[$userId] = $conn;
            echo "New connection! ({$conn->resourceId}) by user {$userId}\n";
        } else {
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (isset($data['type']) && $data['type'] === 'message' && isset($this->userLookup[$data['targetUserId']])) {
            $targetConn = $this->userLookup[$data['targetUserId']];
            if ($targetConn) {
                $targetConn->send(json_encode(['from' => array_search($from, $this->userLookup), 'message' => $data['message']]));
            }
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

    private function verifySession($token) {
        $conn = getDatabaseConnection();

        try {
            $stmt = $conn->prepare("SELECT user_id FROM user_sessions WHERE token = :token AND expires_at > NOW()");
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            $userId = $stmt->fetchColumn();

            if ($userId) {
                return $userId;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Database error in verifySession: " . $e->getMessage());
            return false;
        }
    }
}

