<?php

require_once '../rabbitmqphp_example/databaseFunctions.php';

header('Content-Type: application/json'); // Ensure the header is set for JSON output

// Start session management
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

// Setup error handling and logging
ini_set('display_errors', 0); // Consider turning this on in development (1) and off in production (0)
error_reporting(E_ALL); // Consider E_ALL in development
ini_set('log_errors', 1);

// Establish database connection
$db = getDatabaseConnection();

// Fetch user ID from session
$userId = $_SESSION['user_id'];

// Prepare a statement to fetch friends, excluding the current user from results directly in SQL
$stmt = $db->prepare("
    SELECT u.id, u.username, u.online_status
    FROM friends f
    JOIN users u ON (u.id = f.user_id1 OR u.id = f.user_id2) AND u.id != :user_id
    WHERE (f.user_id1 = :user_id OR f.user_id2 = :user_id) AND f.status = 'accepted'
");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();

$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output the friends JSON
echo json_encode(['status' => 'success', 'data' => $friends]);

// Close the database connection
$db = null;

?>

