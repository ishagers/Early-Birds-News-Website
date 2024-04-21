<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$db = getDatabaseConnection();

$userId = $_SESSION['user_id'];  // Assuming the user's ID is stored in the session.

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

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Encode the array to JSON and output it
echo json_encode($messages);

$db = null;  // Close the connection
?>

