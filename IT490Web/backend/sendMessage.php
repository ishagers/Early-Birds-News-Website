<?php
include '../rabbitmqphp_example/databaseFunctions.php';  // Updated path to databaseFunctions.php

$user_id = mysqli_real_escape_string($db, $_POST['user_id']);  // Assume user_id is posted by the client
$message = mysqli_real_escape_string($db, $_POST['message']);

$sql = "INSERT INTO chat_messages (user_id, message) VALUES ('$user_id', '$message')";
if ($db->query($sql) === TRUE) {
    echo "Message sent successfully";
} else {
    echo "Error: " . $sql . "<br>" . $db->error;
}
$db->close();
?>

