<?php

require_once '../rabbitmqphp_example/databaseFunctions.php';

// Start session management
session_start();

// Check if user is authenticated
if (!isset($_SESSION['username'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$username = $_SESSION['username'];
$db = getDatabaseConnection();

try {
    $friends = fetchFriendsByUsername($db, $username);

    header('Content-Type: application/json');
    echo json_encode($friends);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}

$db = null;

?>

