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
        $conn->beginTransaction(); // Start transaction

        // Assuming getUserIdByUsername is defined and working correctly
        $receiver_id = getUserIdByUsername($conn, $receiver_username);
        $status = $response === 'accept' ? 'accepted' : 'rejected';

        $stmt = $conn->prepare("UPDATE friends SET status = ? WHERE user_id1 = ? AND user_id2 = ?");
        $stmt->execute([$status, $requester_id, $receiver_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "Friend request " . $status . " successfully.";
            $conn->commit(); // Commit the transaction
        } else {
            $_SESSION['message'] = "Failed to update friend request.";
            $conn->rollback(); // Rollback the transaction
        }
    } catch (Exception $e) {
        $conn->rollback(); // Ensure rollback on error
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }

    header('Location: accountPreferences.php?friendsUpdated=true');
    exit;
}

?>

