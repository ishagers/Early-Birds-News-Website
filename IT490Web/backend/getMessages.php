<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

// Start session management
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

// Setup error handling and logging
ini_set('display_errors', 0);
error_reporting(0);
ini_set('log_errors', 1);

// Establish database connection
$db = getDatabaseConnection();

// Fetch user ID from session
$userId = $_SESSION['user_id'];

// Prepare a statement to fetch messages with user information
$stmt = $db->prepare("
    SELECT pm.message, pm.created_at, u.username
    FROM private_messages pm
    JOIN users u ON u.id = pm.sender_id
    WHERE pm.receiver_id = :user_id OR pm.sender_id = :user_id
    ORDER BY pm.created_at DESC
    LIMIT 50
");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();

// Fetch messages
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check and handle if no messages are found
if (empty($messages)) {
    echo json_encode(['message' => 'No messages found']);
    exit;
}

// Encode the messages array to JSON
$json = json_encode($messages);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON encode error: ' . json_last_error_msg());
    echo json_encode(['error' => 'An error occurred while encoding JSON.']);
    exit;
}

// Output JSON encoded messages
echo $json;

// Close the database connection
$db = null;
?>

