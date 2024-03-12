<?php
// Database connection parameters
$servername = "10.147.17.233";
$username = "IT490DB";
$password = "IT490DB";
$dbname = "EARLYBIRD";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data); // Removes whitespace from both sides
    $data = stripslashes($data); // Removes backslashes
    $data = htmlspecialchars($data); // Converts special characters to HTML entities
    return $data;
}

// Determine action based on POST parameters and perform validation & sanitization
if (isset($_POST['article_id']) && isset($_POST['rating'])) {
    // Sanitize input
    $articleId = sanitizeInput($_POST['article_id']);
    $rating = sanitizeInput($_POST['rating']);

    // Validate input
    if (!is_numeric($articleId) || !is_numeric($rating)) {
        die("Invalid input for rating.");
    }
    if ($rating < 1 || $rating > 5) { // Assuming rating is on a scale of 1 to 5
        die("Rating value out of range.");
    }

    // Proceed with database operation...

} elseif (isset($_POST['user_id']) && isset($_POST['topics'])) {
    // Sanitize input
    $userId = sanitizeInput($_POST['user_id']);
    $topics = sanitizeInput($_POST['topics']);

    // Validate input
    if (!is_numeric($userId)) {
        die("Invalid input for user ID.");
    }
    // Further validation on topics could be added based on expected format

    // Proceed with database operation...

} else {
    echo "Invalid request";
}

// Close database connection
$conn->close();
?>
