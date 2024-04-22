<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';
header('Content-Type: application/json');  // Set correct content type

session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$username = $_SESSION['username'];
$conn = getDatabaseConnection();  // Assuming this returns a PDO connection

try {
    $friends = fetchFriendsByUsername($conn, $username);
    if (!empty($friends)) {
        echo json_encode(['status' => 'success', 'data' => $friends]);
    } else {
        echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No friends found']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching friends: ' . $e->getMessage()]);
}
?>

