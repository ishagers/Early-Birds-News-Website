<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

// Prepare a statement to fetch messages with user information
$stmt = $db->prepare("SELECT u.username, m.message, m.timestamp FROM chat_messages m INNER JOIN users u ON m.user_id = u.id ORDER BY m.id DESC LIMIT 20");
$stmt->execute();
$result = $stmt->get_result();

$messages = array();
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);

$stmt->close();
$db->close();
?>
