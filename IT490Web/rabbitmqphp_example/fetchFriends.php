<?php
require 'session.php'; // Handle sessions
require 'databaseFunctions.php'; // Include database functions

checkLogin(); // Ensure the user is logged in

header('Content-Type: application/json'); // Set the header so that the browser treats this as a JSON response

$friendsList = fetchFriendsByUsername(getDatabaseConnection(), $_SESSION['username']);

echo json_encode($friendsList); // Encode the data as JSON and output it
?>

