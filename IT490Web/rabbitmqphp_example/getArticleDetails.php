<?php

require('session.php');
require('databaseFunctions.php');

// Ensure the user is logged in
checkLogin();

//submit comment
if (isset($_POST['submitComment']) && !empty($_POST['comment']) && isset($_GET['id'])) {
    $commentContent = $_POST['comment'];
    $articleId = $_GET['id'];
    $username = $_SESSION['username']; // Assuming you store user's ID in session

    //Setting array and its values to send to RabbitMQ
    $queryValues = array();

    $queryValues['type'] = 'create_comment';
    $queryValues['articleId'] = $articleId;
    $queryValues['content'] = $commentContent;
    $queryValues['username'] = $username;

    //Printing Array and executing SQL Publisher function
    //print_r($queryValues);
    $result = publisher($queryValues);

    //If returned 0, it means it was pushed to the database. Otherwise, echo error
    if ($result == 0) {
        // Use JavaScript for redirect to ensure the alert is shown before redirecting
        echo "<script>alert('Comment successfully made'); window.location.href = 'mainMenu.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error'); window.location.href='mainMenu.php';</script>";
        exit();
    }
}

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

        // Fetch and display ratings
        $averageRatingResponse = getAverageRatingByArticleId($articleId);
        if ($averageRatingResponse['status']) {
            echo "<div id='ratings'>";
            echo "<h3>Average Rating: " . htmlspecialchars($averageRatingResponse['averageRating']) . "</h3>";
            echo "</div>";
        } else {
            echo "<p>No ratings yet.</p>";
        }

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

        echo "<div id='submit-comment'>";
        echo "<h3>Add a comment</h3>";
        echo "<form action='mainMenu.php?id=" . htmlspecialchars($articleId) . "' method='post'>";
        echo "<textarea name='comment' required></textarea>";
        echo "<button type='submit' name='submitComment'>Submit Comment</button>";
        echo "</form>";
        echo "</div>";

        // Optionally, display comments and other details here
    } else {
        echo "<p>Article not found.</p>";
    }
} else {
    echo "<p>No article ID provided.</p>";
}