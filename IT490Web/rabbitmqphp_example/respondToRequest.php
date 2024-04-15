<?php

require 'databaseFunctions.php';

session_start();

// Function to log and set error messages
function handleError($message, $redirect = true) {
    error_log($message);
    $_SESSION['message'] = $message;
    if ($redirect) {
        header('Location: accountPreferences.php');
        exit;
    }
}

if (isset($_POST['response'], $_POST['requester']) && isset($_SESSION['username'])) {
    $conn = getDatabaseConnection();

    $response = htmlspecialchars($_POST['response'], ENT_QUOTES, 'UTF-8');
    $requesterUsername = htmlspecialchars($_POST['requester'], ENT_QUOTES, 'UTF-8');
    $receiverUsername = $_SESSION['username'];

    // Validate the response action
    if (!in_array($response, ['accept', 'reject'], true)) {
        handleError("Invalid response action.");
    }

    // Depending on action, process accordingly
    $status = $response === 'accept' ? 'accepted' : 'rejected';
    $result = updateFriendRequestStatus($conn, $requesterUsername, $receiverUsername, $status);

    if ($result['success']) {
        $_SESSION['message'] = "Friend request {$response}ed.";
    } else {
        handleError("Error {$response}ing request: " . $result['message']);
    }

    header('Location: accountPreferences.php');
    exit;
} else {
    handleError("Invalid request or action.");
}
?>

