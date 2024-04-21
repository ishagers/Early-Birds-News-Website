<?php

// Check if a session is not already active and start it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if the user is logged in
function checkLogin() {
    // Check for either username or user_id
    if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
        echo "<script>alert('Please log in first!');</script>";
        // Use header to redirect and then exit to stop further script execution
        header("Refresh: 0.1; url=../index.php");
        exit();  // Ensure the script stops executing after redirection
    }
}

