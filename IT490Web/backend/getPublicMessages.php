<?php
header('Content-Type: application/json');  // Set the header to return JSON

require_once '../rabbitmqphp_example/databaseFunctions.php';

try {
    $db = getDatabaseConnection();  // Establish a database connection

    $lastMessageId = isset($_GET['lastMessageId']) ? intval($_GET['lastMessageId']) : 0;

    // Prepare the SQL statement using placeholders
    $stmt = $db->prepare("SELECT id, user_id, message, timestamp FROM public_messages WHERE id > ? ORDER BY id ASC");
    $stmt->bindParam(1, $lastMessageId, PDO::PARAM_INT);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $messages
    ]);
} catch (Exception $e) {
    // Handle any exceptions/errors
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching messages: ' . $e->getMessage()
    ]);
} finally {
    if ($db) {
        $db = null;  // Close the database connection
    }
}
?>

