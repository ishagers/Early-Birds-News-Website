<?php
require 'databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['deleteFriendUsername'])) {
    $conn = getDatabaseConnection();
    $currentUsername = $_SESSION['username'];
    $friendUsername = $_POST['deleteFriendUsername'];

    $result = deleteFriend($conn, $currentUsername, $friendUsername);

    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
    } else {
        $_SESSION['message'] = $result['message'];
    }

    header('Location: accountPreferences.php'); // Redirect to a page that lists friends
    exit;
}
?>

