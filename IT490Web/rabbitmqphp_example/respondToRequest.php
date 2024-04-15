<?php

require 'databaseFunctions.php';

session_start();

if (isset($_POST['response'], $_POST['requester']) && isset($_SESSION['username'])) {
    $conn = getDatabaseConnection();
    $response = filter_var($_POST['response'], FILTER_SANITIZE_STRING);
    $requesterId = filter_var($_POST['requester'], FILTER_VALIDATE_INT);  // Validate and sanitize input as integer
    $receiverUsername = $_SESSION['username'];

    if (!in_array($response, ['accept', 'reject'], true)) {
        $_SESSION['message'] = "Invalid response action.";
        header('Location: accountPreferences.php');
        exit;
    }

    // Depending on action, process accordingly
    if ($response === 'accept' || $response === 'reject') {
        $status = $response === 'accept' ? 'accepted' : 'rejected';
        $result = updateFriendRequestStatus($conn, $requesterId, $receiverUsername, $status);
        $_SESSION['message'] = $result ? "Friend request {$response}ed." : "Error {$response}ing request.";
    } else {
        $_SESSION['message'] = "Invalid response action.";
    }

    header('Location: accountPreferences.php');
    exit;
} else {
    $_SESSION['message'] = "Invalid request or action.";
    header('Location: accountPreferences.php');
    exit;
}

?>

