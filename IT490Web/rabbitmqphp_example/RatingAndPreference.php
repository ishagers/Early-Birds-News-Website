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

// Handle rating submission
if (isset($_POST['submitRating'])) {
    if (isset($_POST['article_id']) && isset($_POST['rating'])) {
        $articleId = sanitizeInput($_POST['article_id']);
        $rating = sanitizeInput($_POST['rating']);
        $userId = $_SESSION['user_id']; // Ensure you have the user's ID stored in session

        // Validate input
        if (!is_numeric($articleId) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
            die("Invalid input for rating. Rating must be between 1 and 5.");
        }

        // Check if the user has already rated this article
        $checkRatingSql = "SELECT id FROM ratings WHERE article_id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkRatingSql);
        $stmt->bind_param("ii", $articleId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User has already rated, update the rating
            $updateSql = "UPDATE ratings SET rating = ? WHERE article_id = ? AND user_id = ?";
        } else {
            // New rating
            $updateSql = "INSERT INTO ratings (rating, article_id, user_id) VALUES (?, ?, ?)";
        }

        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("iii", $rating, $articleId, $userId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Rating successfully submitted.";
        } else {
            echo "Failed to submit rating.";
        }
    } else {
        echo "Invalid request";
    }
}

// Close database connection
$conn->close();
?>

