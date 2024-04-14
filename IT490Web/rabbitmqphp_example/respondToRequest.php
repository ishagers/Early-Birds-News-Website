<?php
require 'databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if (isset($_POST['response'], $_POST['requester_id'])) {
    $conn = getDatabaseConnection();
    $response = $_POST['response'];
    $requester_id = $_POST['requester_id'];
    $receiver_username = $_SESSION['username'];

    try {
        $receiver_id = getUserIdByUsername($conn, $receiver_username);
        $status = $response === 'accept' ? 'accepted' : 'rejected';
        $stmt = $conn->prepare("UPDATE friends SET status = ? WHERE user_id1 = ? AND user_id2 = ?");
        $stmt->execute([$status, $requester_id, $receiver_id]);

        if ($stmt->rowCount() > 0) {
            echo "Friend request " . $status;
        } else {
            echo "Failed to update friend request.";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    header('Location: accountPreferences.php');
    exit;
}

?>

