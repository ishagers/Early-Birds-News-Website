<?php

require 'databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['friend_username'])) {  
    $conn = getDatabaseConnection();
    $currentUsername = $_SESSION['username'];
    $friendUsername = $_POST['friend_username']; 

    $result = deleteFriend($conn, $currentUsername, $friendUsername);

    if ($result['success']) {
        $_SESSION['message'] = "Friend deleted successfully: " . $result['message'];
    } else {
        $_SESSION['message'] = "Failed to delete friend: " . $result['message'];
    }

    header('Location: accountPreferences.php?friendsUpdated=true');
    exit;
} else {
    $_SESSION['message'] = "No data submitted.";
    header('Location: accountPreferences.php');
    exit;
}

?>

