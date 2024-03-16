<?php
include 'newsFetcher.php';
require('session.php');
require('databaseFunctions.php');

 if (isset($_GET['id'])) {
	if (isset($_POST['submitComment']) && !empty($_POST['comment'])) {
	    $commentContent = $_POST['comment'];
	    // Assuming $userId is fetched earlier as shown
	    $result = submitComment($articleId, $commentContent, $username);

	    // Redirect to the home menu after showing an alert with the result message
	    echo "<script>alert('".$result['message']."'); window.location.href = 'mainMenu.php';</script>";
if (isset($_GET['id'])) {
	}

	if ($article && $article['status']) {
	    // Your existing code to display the article, comments, rating form, etc.
	}
if ($article && $article['status']) {
    // Article title, content, and publication date display logic...
if ($article && $article['status']) {
    echo "<p>" . nl2br(htmlspecialchars($article['article']['content'])) . "</p>";
    echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date']) . "</small>";

	$apiKey = '898d8c1625884af1a9774e9662cb980d';
	$newsUrl = 'https://newsapi.org/v2/top-headlines?country=us&apiKey=' . $apiKey;

	// Use file_get_contents or cURL to fetch news data
	$response = file_get_contents($newsUrl);
	if ($response) {
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

?>
