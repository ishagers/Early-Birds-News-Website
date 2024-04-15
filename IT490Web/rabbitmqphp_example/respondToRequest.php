<?php

require 'databaseFunctions.php';

session_start();

if (isset($_POST['response'], $_POST['requester']) && isset($_SESSION['username'])) {
    $conn = getDatabaseConnection();
    $response = $_POST['response'];
    $requesterUsername = $_POST['requester'];
    $receiverUsername = $_SESSION['username'];

    // Sanitize inputs
    $response = filter_var($response, FILTER_SANITIZE_STRING);
    $requesterUsername = filter_var($requesterUsername, FILTER_SANITIZE_STRING);
    $receiverUsername = filter_var($receiverUsername, FILTER_SANITIZE_STRING);

    // Validate inputs
    if (!in_array($response, ['accept', 'reject'], true)) {
        $_SESSION['message'] = "Invalid response action.";
        header('Location: accountPreferences.php');
        exit;
    }

    // Process the response
    if ($response === 'accept') {
        // Update friend request to 'accepted'
        $result = updateFriendRequestStatus($conn, $requesterUsername, $receiverUsername, 'accepted');
        $_SESSION['message'] = $result ? "Friend request accepted." : "Error accepting request.";
    } elseif ($response === 'reject') {
        // Update friend request to 'rejected'
        $result = updateFriendRequestStatus($conn, $requesterUsername, $receiverUsername, 'rejected');
        $_SESSION['message'] = $result ? "Friend request rejected." : "Error rejecting request.";
    }

    header('Location: accountPreferences.php');
    exit;
} else {
    // Redirect if the necessary data is not set
    $_SESSION['message'] = "Invalid request or action.";
    header('Location: accountPreferences.php');
    exit;
}

?>

