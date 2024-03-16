<?php
header('Content-Type: text/html; charset=utf-8');
include 'newsFetcher.php'; // Ensure this file exists
require('session.php');
require('databaseFunctions.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define the consoleLog function at the beginning of your script
function consoleLog($message) {
    // Sanitize the message to escape any single quotes
    $sanitizedMessage = str_replace("'", "\'", $message);
    // Generate and echo a JavaScript snippet that logs to the console
    echo "<script>console.log('$sanitizedMessage');</script>";
}

// Use the consoleLog function to log messages
consoleLog("Start of getArticleDetails");

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    $username = $_SESSION['username']; // Assuming username is stored in session upon login
    $article = getArticleById($articleId); // Fetch the article details
    consoleLog("At debug 1");

    // Handle comment submission
    if (isset($_POST['submitComment']) && !empty($_POST['comment'])) {
        $commentContent = $_POST['comment'];
        $result = submitComment($articleId, $commentContent, $username); // Assuming submitComment uses username

        // Redirect with message after submission
        echo "<script>alert('" . htmlspecialchars($result['message']) . "'); window.location.href = 'mainMenu.php';</script>";
        consoleLog("Debug information: 2");
        exit();
    }
    consoleLog("Debug information: 3");
    if ($article && $article['status']) {
        // Display the article content, related news, and rating form
        echo "<h2>" . htmlspecialchars($article['article']['title']) . "</h2>";
        echo "<p>" . nl2br(htmlspecialchars($article['article']['content'])) . "</p>";
        echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date']) . "</small>";
        consoleLog("Debug information: 4");
	$apiKey = '898d8c1625884af1a9774e9662cb980d';
	$newsUrl = 'https://newsapi.org/v2/top-headlines?country=us&apiKey=' . $apiKey;

	// Use file_get_contents or cURL to fetch news data
	$response = file_get_contents($newsUrl);
	if ($response) {
        consoleLog("Debug information: 5");
	    $newsData = json_decode($response, true);
	    if ($newsData['status'] == 'ok') {
		echo "<div class='news-section'>";
		echo "<h3>Related News</h3>";
		foreach ($newsData['articles'] as $article) {
		    echo "<div class='news-article'>";
		    echo "<h4><a href='" . htmlspecialchars($article['url']) . "'>" . htmlspecialchars($article['title']) . "</a></h4>";
		    echo "<p>" . htmlspecialchars($article['description']) . "</p>";
		    echo "</div>";
		}
		echo "</div>";
	    }
        consoleLog("Debug information: 6");
	}
	else{
        echo "<p>Error at newsdata</p>";
    }


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
