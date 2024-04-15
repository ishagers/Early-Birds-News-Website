<?php

require_once 'databaseFunctions.php';

// Check if the session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the username is set in the session to customize the greeting
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$ebpPoints = isset($_SESSION['username']) ? fetchUserEBP($_SESSION['username']) : 0;

if (isset($_POST['add_ebp'])) {
    if (isset($_SESSION['username'])) {
        $currencyResponse = addCurrencyToUserByUsername($_SESSION['username'], 5);
        echo "<p>" . htmlspecialchars($currencyResponse['message']) . "</p>";
    } else {
        echo "<p>User must be logged in to receive currency.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            height: 400px;
            background-color: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            padding: 10px;
            display: none; /* Initially hidden */
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 10px;
        }

        .chat-input {
            width: calc(100% - 20px); /* accounting for padding */
            margin-bottom: 5px;
        }

        button {
            align-self: center;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Early Bird Articles</h1>
    <div class="user-info">
        Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
        <form method="post" action="">
            <button type="submit" name="add_ebp">Add 5 EBP</button>
        </form>
        <img src="../assets/EBP.png" alt="EB Points:" width="32" height="32" />
        <span class="eb-points-label">EB Points:</span>
        <span id="ebpPoints" class="eb-points"><?php echo $ebpPoints; ?></span>
    </div>
</div>

<div class="nav-bar

