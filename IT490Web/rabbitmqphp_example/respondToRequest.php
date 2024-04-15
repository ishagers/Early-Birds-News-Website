<?php

require 'databaseFunctions.php';

session_start();

if (isset($_POST['response'], $_POST['requester']) && isset($_SESSION['username'])) {
    $conn = getDatabaseConnection();
   $response = htmlspecialchars($response, ENT_QUOTES, 'UTF-8');
   $requesterUsername = htmlspecialchars($requesterUsername, ENT_QUOTES, 'UTF-8');
   $receiverUsername = htmlspecialchars($receiverUsername, ENT_QUOTES, 'UTF-8');


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

