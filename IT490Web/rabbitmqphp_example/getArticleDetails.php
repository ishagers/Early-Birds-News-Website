<?php

require('session.php');
require('databaseFunctions.php');
require_once('SQLPublish.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

checkLogin();

$username = $_SESSION['username'];
$articleId = $userId = null;
$article = null;

// Check if an article ID is provided and fetch the article
if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    $article = getArticleById($articleId); // Assume this function returns the article array with a 'status' key
}

// Now that you have the article details, you can handle the share request
if (isset($_POST['submitShare']) && !empty($_POST['shareEmail']) && $article && $article['status']) {
    $recipientEmail = sanitizeInput($_POST['shareEmail']); // Sanitize the email input
    $articleTitle = htmlspecialchars($article['article']['title']);
    $articleContent = nl2br(htmlspecialchars($article['article']['content']));
    $articleUrl = ""; // If you have an article URL, include it here

    // Call the SendArticle function
    $emailResponse = SendArticle($recipientEmail, $articleTitle, $articleContent, $articleUrl);

    // Feedback message from attempting to send the article
    echo "<p>" . $emailResponse['message'] . "</p>"; // Display feedback message
}

// Display rating submission feedback
if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']); // Clear the message after displaying
}

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];

    try {
        $pdo = getDatabaseConnection();
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $userStmt->execute(['username' => $username]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];
	    $article = getArticleById($articleId);
            // Process comment submission
            if (isset($_POST['submitComment']) && !empty($_POST['comment'])) {
                // Assume submitComment() is a function that processes the comment submission
                $commentResponse = submitComment($articleId, sanitizeInput($_POST['comment']), $username);
                echo "<p>" . $commentResponse['message'] . "</p>"; // Display feedback from comment submission
            }
        }
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }

}
if ($article && $article['status']) {
    // Article title, content, and publication date display logic...
    echo "<h2>" . htmlspecialchars($article['article']['title']) . "</h2>";
    echo "<p>" . nl2br(htmlspecialchars($article['article']['content'])) . "</p>";
    echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date']) . "</small>";

    // Ratings display logic...
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

    // Add a comment form
    echo "<div id='submit-comment'>";
    echo "<h3>Add a comment</h3>";
    echo "<form action='getArticleDetails.php?id=" . htmlspecialchars($articleId) . "' method='post'>";
    echo "<textarea name='comment' required></textarea>";
    echo "<button type='submit' name='submitComment'>Submit Comment</button>";
    echo "</form>";
    echo "</div>";

    // Add rating submission form here
    echo "<div id='article-rating'>";
    echo "<h3>Rate this Article</h3>";
    echo "<form action='RatingAndPreference.php' method='POST'>";
    echo "<input type='hidden' name='article_id' value='" . htmlspecialchars($articleId) . "'>";
    echo "<label for='rating'>Rating:</label>";
    echo "<select name='rating' id='rating' required>";
    echo "<option value='1'>1</option>";
    echo "<option value='2'>2</option>";
    echo "<option value='3'>3</option>";
    echo "<option value='4'>4</option>";
    echo "<option value='5'>5</option>";
    echo "</select>";
    echo "<input type='submit' name='submitRating' value='Submit Rating'>";
    echo "</form>";
    echo "</div>";

    echo "<div id='share-article'>";
echo "<h3>Share this Article</h3>";
echo "<form action='getArticleDetails.php?id=" . htmlspecialchars($articleId) . "' method='post'>";
echo "<input type='email' name='shareEmail' placeholder='Enter email to share' required>";
echo "<button type='submit' name='submitShare'>Share Article</button>";
echo "</form>";
echo "</div>";

} else {
    echo "<p>Article not found.</p>";
}

