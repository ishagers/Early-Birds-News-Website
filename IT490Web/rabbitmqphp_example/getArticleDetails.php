<?php

require('session.php');
require('databaseFunctions.php');

checkLogin();

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];

    $article = getArticleById($articleId);

    if ($article['status']) {
        echo "<h2>" . htmlspecialchars($article['title']) . "</h2>";
        echo "<p>" . nl2br(htmlspecialchars($article['content'])) . "</p>";
        echo "<small>Published on: " . htmlspecialchars($article['publication_date']) . "</small>";

        $comments = getCommentsByArticleId($articleId);
        if (!empty($comments)) {
            echo "<div id='comments'>";
            echo "<h3>Comments</h3>";
            foreach ($comments as $comment) {
                // Assuming $comment array has 'content' and 'username'
                echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['content']) . "</p>";
            }
            echo "</div>";
        } else {
            echo "<p>No comments yet.</p>";
        }

        $ratings = getRatingsByArticleId($articleId);
        if ($ratings['status']) {
            echo "<div id='ratings'>";
            echo "<h3>Average Rating: " . htmlspecialchars($ratings['average']) . "</h3>";
            echo "</div>";
        } else {
            echo "<p>No ratings yet.</p>";
        }

    } else {
        echo "<p>Article not found.</p>";
    }
} else {
    echo "<p>No article ID provided.</p>";
}

?>
