<?php
include 'newsFetcher.php'; // Ensure this file exists and is correctly included
require('session.php');
require('databaseFunctions.php');
require_once('SQLPublish.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
checkLogin();
$username = $_SESSION['username'];

$articleId = $userId = null;

// Get article details if an ID is provided
if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    try {
        $pdo = getDatabaseConnection();
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $userStmt->execute(['username' => $username]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];
            $article = getArticleById($articleId, $pdo); // Make sure this function is defined and works
        }
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }
}

// Handle comment submission
if (isset($_POST['submitComment']) && !empty($_POST['comment']) && $userId) {
    $commentContent = $_POST['comment'];
    $result = submitComment($articleId, $commentContent, $userId); // Use submitComment

    // Redirect to the home menu after showing an alert with the result message
    echo "<script>alert('" . htmlspecialchars($result['message']) . "'); window.location.href = 'mainMenu.php';</script>";
    exit();
}

if ($article && $article['status']) {
    // Article title, content, and publication date display logic...
    echo "<h2>" . htmlspecialchars($article['article']['title']) . "</h2>";
    echo "<p>" . nl2br(htmlspecialchars($article['article']['content'])) . "</p>";
    echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date']) . "</small>";

    // News fetching logic...
    // Ensure that newsFetcher.php defines how to fetch news and that $apiKey is valid
    // Make sure file_get_contents is allowed to fetch external content on your server

    // Ratings display logic...
    // Make sure getAverageRatingByArticleId() function exists and returns the correct data
    $averageRatingResponse = getAverageRatingByArticleId($articleId);
    if ($averageRatingResponse['status']) {
        echo "<div id='ratings'>";
        echo "<h3>Average Rating: " . htmlspecialchars($averageRatingResponse['averageRating']) . "</h3>";
        echo "</div>";
    } else {
        echo "<p>No ratings yet.</p>";
    }

    // Comments display logic...
    // Make sure getCommentsByArticleId() function exists and returns the correct data
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

    // Comment submission form
    echo "<div id='submit-comment'>";
    echo "<h3>Add a comment</h3>";
    echo "<form action='getArticleDetails.php?id=" . htmlspecialchars($articleId) . "' method='post'>";
    echo "<textarea name='comment' required></textarea>";
    echo "<button type='submit' name='submitComment'>Submit Comment</button>";
    echo "</form>";
    echo "</div>";

    // Rating submission form
    // Make sure the RatingAndPreference.php file exists and can handle the POST request for submitting ratings
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

} else {
    echo "<p>Article not found.</p>";
}

?>
