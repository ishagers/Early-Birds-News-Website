<?php
header('Content-Type: text/html; charset=utf-8');
require('session.php');
require('databaseFunctions.php');
//require 'newsFetcher.php'; // Ensure this file exists
ini_set('display_errors', 1);
error_reporting(E_ALL);

checkLogin();

$username = $_SESSION['username'];
$articleId = null;

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    $article = getArticleById($articleId); // Ensure this function correctly fetches article data

    // Handle comment submission
    if (isset($_POST['submitComment']) && !empty($_POST['comment'])) {
        $commentContent = $_POST['comment'];
        // Assuming the submitComment function correctly handles the comment submission
        $result = submitComment($articleId, $commentContent, $username); 

        echo "<script>alert('" . htmlspecialchars($result['message']) . "'); window.location.href = 'mainMenu.php';</script>";
        exit();
    }

    if ($article && $article['status']) {
        // Display the article content
        echo "<h2>" . htmlspecialchars($article['article']['title']) . "</h2>";
        echo "<p>" . nl2br(htmlspecialchars($article['article']['content'])) . "</p>";
        echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date']) . "</small>";

        // Include the news fetcher script to display related news
        include 'newsFetcher.php'; // 

    // Ratings display logic...
    $averageRatingResponse = getAverageRatingByArticleId($articleId);
    if ($averageRatingResponse['status']) {
    echo "</form>";
    echo "</div>";

    // Add rating submission form
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
} else {
    echo "<p>No article ID provided.</p>";
}

?>
