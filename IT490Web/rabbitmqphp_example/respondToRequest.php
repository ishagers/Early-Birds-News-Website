<?php

require 'databaseFunctions.php';

session_start();

if (isset($_POST['response'], $_POST['requester']) && isset($_SESSION['username'])) {
    $conn = getDatabaseConnection();
    $response = $_POST['response'];  // Accept or Reject
    $requesterUsername = $_POST['requester'];
    $receiverUsername = $_SESSION['username'];

    // Log received data for debugging
    error_log("Received data - Response: {$response}, Requester: {$requesterUsername}, Receiver: {$receiverUsername}");

    // Check the response type
    if (!in_array($response, ['accept', 'reject'], true)) {
        $_SESSION['message'] = "Invalid response action.";
        header('Location: accountPreferences.php');
        exit;
    }

    // Process the response
    if ($response === 'accept') {
        $result = updateFriendRequestStatus($conn, $requesterUsername, $receiverUsername, 'accepted');
        $actionWord = "accepted";
    } elseif ($response === 'reject') {
        $result = rejectFriendRequest($conn, $requesterUsername, $receiverUsername);
        $actionWord = "rejected";
    } else {
        $_SESSION['message'] = "Invalid response action.";
        header('Location: accountPreferences.php');
        exit;
    }

    if ($result['success']) {
        $_SESSION['message'] = "Friend request {$actionWord}.";
    } else {
        $_SESSION['message'] = "Error {$actionWord}ing request: " . $result['message'];
    }

    header('Location: accountPreferences.php');
    exit;
} else {
    $_SESSION['message'] = "Invalid request or action.";
    header('Location: accountPreferences.php');
    exit;
}

?>

