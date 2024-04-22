<?php
require_once '../rabbitmqphp_example/databaseFunctions.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    // Return an error if the user is not logged in
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$username = $_SESSION['username'];
$conn = getDatabaseConnection(); // Make sure this function properly initializes and returns a PDO connection.

try {
    $friends = fetchFriendsByUsername($conn, $username);
    echo json_encode(['status' => 'success', 'data' => $friends]);
} catch (Exception $e) {
    // Handle exception by sending a JSON error message
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

