<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

$db = getDatabaseConnection();

// Retrieve the last message ID from the GET request
$lastMessageId = isset($_GET['lastMessageId']) ? intval($_GET['lastMessageId']) : 0;

// Fetch only messages with an ID greater than the lastMessageId
$stmt = $db->prepare("
    SELECT pm.id, pm.message, pm.timestamp, u.username
    FROM public_messages pm
    JOIN users u ON u.id = pm.user_id
    WHERE pm.id > ?
    ORDER BY pm.id ASC
    LIMIT 50
");
$stmt->bindParam(1, $lastMessageId, PDO::PARAM_INT);
$stmt->execute();

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);

$db = null;  // Close the connection
?>

