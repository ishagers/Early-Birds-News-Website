<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

$db = getDatabaseConnection();

// Use the 'timestamp' column instead of 'created_at'
$stmt = $db->prepare("
    SELECT pm.message, pm.timestamp, u.username
    FROM public_messages pm
    JOIN users u ON u.id = pm.user_id
    ORDER BY pm.timestamp DESC
    LIMIT 50
");
$stmt->execute();

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);

$db = null;  // Close the connection
?>
