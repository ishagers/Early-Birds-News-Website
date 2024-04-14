<?php

require 'databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if (isset($_POST['response'], $_POST['requester_id']) && $_POST['response'] == 'accept') {
    $conn = getDatabaseConnection();
    $requester_id = $_POST['requester_id'];
    $receiver_username = $_SESSION['username'];

    $result = acceptFriendRequest($conn, $requester_id, $receiver_username);

    $_SESSION['message'] = $result['message'];
    header('Location: accountPreferences.php?friendsUpdated=true');
    exit;
} else {
    // Handle other cases or invalid access
    $_SESSION['message'] = "Invalid request or action.";
    header('Location: accountPreferences.php');
    exit;
}

?>

