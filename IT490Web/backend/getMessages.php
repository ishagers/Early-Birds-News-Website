<?php
include '../rabbitmqphp_example/databaseFunctions.php';

$result = $db->query("SELECT users.username, chat_messages.message, chat_messages.timestamp FROM chat_messages INNER JOIN users ON chat_messages.user_id = users.id ORDER BY chat_messages.id DESC LIMIT 20");

$messages = array();
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);
$db->close();
?>

