<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

$db = getDatabaseConnection();

// Prepare a statement to fetch public messages
$stmt = $db->prepare("
    SELECT pm.message, pm.created_at, u.username
    FROM public_messages pm
    JOIN users u ON u.id = pm.user_id
    ORDER BY pm.created_at DESC
    LIMIT 50
");
$stmt->execute();

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Encode the array to JSON and output it
echo json_encode($messages);

$db = null;  // Close the connection
?>

