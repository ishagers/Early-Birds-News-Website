<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

$db = getDatabaseConnection();
$lastMessageId = isset($_GET['lastMessageId']) ? intval($_GET['lastMessageId']) : 0;

// Fetch only messages with an ID greater than the lastMessageId
$stmt = $db->prepare("SELECT * FROM public_messages WHERE id > ? ORDER BY id ASC");
$stmt->bindParam(1, $lastMessageId, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$db = null;  // Close the connection
?>
