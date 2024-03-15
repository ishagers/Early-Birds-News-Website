<?php

require('session.php');
require('databaseFunctions.php');

// Ensure the user is logged in
checkLogin();

// Fetch the article details
if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    $article = getArticleById($articleId);

    // Check if the article fetch was successful
    if ($article['status']) {
        // Display article details
        echo "<h2>" . htmlspecialchars($article['article']['title']) . "</h2>";
        echo "<p>" . nl2br(htmlspecialchars($article['article']['content'])) . "</p>";
        echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date']) . "</small>";

        // Fetch and display comments
        $comments = getCommentsByArticleId($articleId);
        if ($comments['status']) {
            echo "<div id='comments'>";
            echo "<h3>Comments</h3>";
            if (!empty($comments['comments'])) {
                foreach ($comments['comments'] as $comment) {
                    echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . "</p>";
                }
            } else {
                echo "<p>No comments yet.</p>";
            }
            echo "</div>";
        }

        // Fetch and display ratings
        $averageRatingResponse = getAverageRatingByArticleId($articleId);
        if ($averageRatingResponse['status']) {
            echo "<div id='ratings'>";
            echo "<h3>Average Rating: " . htmlspecialchars($averageRatingResponse['averageRating']) . "</h3>";
            echo "</div>";
        } else {
            echo "<p>No ratings yet.</p>";
        }

        // Optionally, display comments and other details here
    } else {
        echo "<p>Article not found.</p>";
    }
} else {
    echo "<p>No article ID provided.</p>";
}