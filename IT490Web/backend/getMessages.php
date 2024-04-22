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

// Get the recipient's ID from the request
$recipientId = $_GET['recipient_id'] ?? null;

if (!$recipientId) {
    echo json_encode(['error' => 'Recipient ID is required']);
    exit;
}

try {
    // Prepare a statement to fetch private messages between the user and the recipient
    $stmt = $db->prepare("
        SELECT u.username, pm.message
        FROM private_messages pm
        JOIN users u ON (pm.sender_id = u.id OR pm.receiver_id = u.id)
        WHERE (pm.sender_id = :userId AND pm.receiver_id = :recipientId)
           OR (pm.sender_id = :recipientId AND pm.receiver_id = :userId)
        ORDER BY pm.id ASC
    ");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':recipientId', $recipientId, PDO::PARAM_INT);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the messages as JSON
    echo json_encode($messages);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}


// Close the database connection
$db = null;
?>
