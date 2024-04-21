<?php

require_once '../rabbitmqphp_example/databaseFunctions.php';

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'You must be logged in to send messages.']);
    exit;
}

$db = getDatabaseConnection();

$username = $_SESSION['username'];
$receiver_username = $_POST['receiver_username'] ?? '';
$message = $_POST['message'] ?? '';

// Validate input
if (empty($receiver_username) || empty($message)) {
    echo json_encode(['error' => 'Receiver or message not provided']);
    exit;
}

try {
    // Get user IDs from usernames
    $sender_id = getUserIdByUsername($db, $username);
    $receiver_id = getUserIdByUsername($db, $receiver_username);

    if ($sender_id === null || $receiver_id === null) {
        echo json_encode(['error' => 'Invalid sender or receiver username']);
        exit;
    }

    // Prepare the SQL to check friendship and insert the message if friends
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
        echo json_encode(['success' => 'Message sent successfully']);
    } else {
        echo json_encode(['error' => 'Could not send message. Are you friends?']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function getUserIdByUsername($db, $username) {
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
}

$db = null;

?>

