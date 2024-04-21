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
    SELECT u.username, u.online_status
    FROM friends f
    JOIN users u ON u.id = f.user_id1 OR u.id = f.user_id2
    WHERE (f.user_id1 = :user_id OR f.user_id2 = :user_id) AND f.status = 'accepted'
");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert the friends array to JSON
$friendsJson = json_encode($friends);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON encode error: ' . json_last_error_msg());
    echo json_encode(['error' => 'An error occurred while encoding JSON.']);
    exit;
}

// Output the friends JSON
echo $friendsJson;

// Close the database connection
$db = null;
?>

