<?php
include 'newsFetcher.php';

require('session.php');

require('databaseFunctions.php');

require_once('SQLPublish.php');

ini_set('display_errors', 1);

error_reporting(E_ALL);

// Ensure the user is logged in
checkLogin();
$username = $_SESSION['username'];

$articleId = $userId = null;

if (isset($_GET['id'])) {

    $articleId = $_GET['id'];
    try {
        $pdo = getDatabaseConnection(); 
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $userStmt->execute(['username' => $username]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);


        if ($user) {

            $userId = $user['id'];

            if (!isset($_SESSION['viewed_articles']) || !in_array($articleId, $_SESSION['viewed_articles'])) {

                $viewStmt = $pdo->prepare("INSERT INTO user_article_views (user_id, article_id) VALUES (:userId, :articleId)");

                $viewStmt->execute(['userId' => $userId, 'articleId' => $articleId]);

                $_SESSION['viewed_articles'][] = $articleId;

            }
            $article = getArticleById($articleId, $pdo);
        }
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }
}
	// Handle comment submission
	if (isset($_POST['submitComment']) && !empty($_POST['comment'])) {
	    $commentContent = $_POST['comment'];
	    // Assuming $userId is fetched earlier as shown
	    $result = submitComment($articleId, $commentContent, $username); // Use submitComment 

	    // Redirect to the home menu after showing an alert with the result message
	    echo "<script>alert('".$result['message']."'); window.location.href = 'mainMenu.php';</script>";
	    exit();
	}

	if ($article && $article['status']) {
	}
if ($article && $article['status']) {
    // Article title, content, and publication date display logic...
    echo "<h2>" . htmlspecialchars($article['article']['title']) . "</h2>";
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
    echo "<div id='-comment'>";
    echo "<h3>Add a comment</h3>";
    echo "<form action='getArticleDetails.php?id=" . htmlspecialchars($articleId) . "' method='post'>";
    echo "<textarea name='comment' required></textarea>";
    echo "<button type='submit' name='submitComment'>Submit Comment</button>";
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
