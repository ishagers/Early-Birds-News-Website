<?php

require('session.php');
require('databaseFunctions.php');

// Ensure the user is logged in
checkLogin();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initial debug message
echo "Test successful.<br/>";

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];

    // Output the received article ID for debugging
    echo "Received article ID: " . htmlspecialchars($articleId) . "<br/>";

    // Fetch the article details
    $article = getArticleById($articleId);

    // Check if the article fetch was successful
    if ($article['status']) {
        // Display article details using null coalescing operator to avoid undefined index errors
        echo "<h2>" . htmlspecialchars($article['article']['title'] ?? 'No title') . "</h2>";
        echo "<p>" . nl2br(htmlspecialchars($article['article']['content'] ?? 'No content')) . "</p>";
        echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date'] ?? 'No publication date') . "</small>";

        // Fetch and display comments
        $comments = getCommentsByArticleId($articleId);
        if ($comments['status'] && !empty($comments['comments'])) {
            echo "<div id='comments'>";
            echo "<h3>Comments</h3>";
            foreach ($comments['comments'] as $comment) {
                echo "<p><strong>" . htmlspecialchars($comment['username'] ?? 'Anonymous') . ":</strong> " . htmlspecialchars($comment['content'] ?? '') . "</p>";
            }
            echo "</div>";
        } else {
            echo "<p>No comments yet.</p>";
        }

        // Fetch and display ratings
        $ratings = getRatingsByArticleId($articleId);
        if ($ratings['status'] && !empty($ratings['ratings'])) {
            echo "<div id='ratings'>";
            // Calculate the average rating; you'll need to implement this logic depending on how ratings are stored
            $averageRating = calculateAverageRating($ratings['ratings']);
            echo "<h3>Average Rating: " . htmlspecialchars($averageRating ?? 'No rating') . "</h3>";
            echo "</div>";
        } else {
            echo "<p>No ratings yet.</p>";
        }

    } else {
        // If article status is false, output the error message
        echo "<p>Article not found. Error: " . htmlspecialchars($article['message']) . "</p>";
    }
} else {
    echo "<p>No article ID provided.</p>";
}

// Function to calculate the average rating; you'll need to implement this based on your data structure
function calculateAverageRating($ratings)
{
    $sum = 0;
    foreach ($ratings as $rating) {
        $sum += $rating['rating'] ?? 0; // Use the rating or 0 if not set
    }
    return $sum > 0 ? $sum / count($ratings) : 'No ratings';
}
