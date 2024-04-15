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

    $status = $response === 'accept' ? 'accepted' : 'declined';
    $result = updateFriendRequestStatus($conn, $requesterUsername, $receiverUsername, $status);

    if ($result['success']) {
        $_SESSION['message'] = "Friend request {$status}.";
    } else {
        $_SESSION['message'] = "Error {$response}ing request: " . $result['message'];
    }

    header('Location: accountPreferences.php');
    exit;
} else {
    $_SESSION['message'] = "Invalid request or action.";
    header('Location: accountPreferences.php');
    exit;
}
?>

