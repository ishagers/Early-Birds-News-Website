<?php

require('session.php');
require('databaseFunctions.php');

// Ensure the user is logged in
checkLogin();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];

    // Output the received article ID for debugging
    echo "Debug: Article ID received - " . htmlspecialchars($articleId) . "<br/>";

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
        echo "Debug: comment found - " . htmlspecialchars($articleId) . "<br/>";
        if ($comments['status']) {
            echo "<div id='comments'>";
            echo "<h3>Comments</h3>";
            if (!empty($comments['comments'])) {
                foreach ($comments['comments'] as $comment) {
                    echo "<p><strong>" . htmlspecialchars($comment['username'] ?? 'Anonymous') . ":</strong> " . htmlspecialchars($comment['content'] ?? '') . "</p>";
                }
            } else {
                echo "<p>No comments yet.</p>";
            }
            echo "</div>";
        } else {
            echo "Debug: Comments Status - " . htmlspecialchars($comments['message']) . "<br/>";
        }

        // Fetch and display ratings
        $ratings = getRatingsByArticleId($articleId);
        if ($ratings['status']) {
            echo "<div id='ratings'>";
            // Calculate the average rating
            $averageRating = calculateAverageRating($ratings['ratings']);
            echo "<h3>Average Rating: " . htmlspecialchars($averageRating ?? 'No rating') . "</h3>";
            echo "</div>";
        } else {
            echo "Debug: Ratings Status - " . htmlspecialchars($ratings['message']) . "<br/>";
        }

    } else {
        // If article status is false, output the error message
        echo "Debug: Article Fetch - " . htmlspecialchars($article['message']) . "<br/>";
    }
} else {
    echo "Debug: No article ID provided.<br/>";
}

// Function to calculate the average rating
function calculateAverageRating($ratings)
{
    $sum = 0;
    $count = 0;
    foreach ($ratings as $rating) {
        $sum += $rating['rating'] ?? 0; // Use the rating or 0 if not set
        $count++;
    }
    // Avoid division by zero
    return $count > 0 ? round($sum / $count, 2) : 'No ratings';
}
