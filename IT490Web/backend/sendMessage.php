<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    die("You must be logged in to send messages.");
}

$db = getDatabaseConnection();

if (isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    // Prepare the SQL to check friendship and insert the message
    $stmt = $db->prepare("
        INSERT INTO private_messages (sender_id, receiver_id, message)
        SELECT :sender_id, :receiver_id, :message
        FROM dual
        WHERE EXISTS (
            SELECT 1
            FROM friends
            WHERE status = 'accepted' AND
                ((user_id1 = :sender_id AND user_id2 = :receiver_id) OR
                 (user_id1 = :receiver_id AND user_id2 = :sender_id))
        )
    ");

    $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
    $stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);

    if ($stmt->execute() && $stmt->rowCount() > 0) {
        echo "Message sent successfully";
    } else {
        echo "Error: Could not send message. Are you friends?";
    }
} else {
    echo "Receiver or message not provided";
}

$db = null;
?>

