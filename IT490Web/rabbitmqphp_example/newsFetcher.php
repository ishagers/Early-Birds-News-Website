<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define API keys and URLs
$apiKey1 = '898d8c1625884af1a9774e9662cb980d';
$newsUrl1 = "https://newsapi.org/v2/top-headlines?country=us&apiKey={$apiKey1}";

$apiKey2 = 'UvENR8ucJtM7ZSpXxUokK3tttamiRut7HDaaXc6Q';
$newsUrl2 = "https://gnews.io/api/v4/top-headlines?token={$apiKey}&lang=en";

// Initialize cURL sessions for both APIs
$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_URL, $newsUrl1);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $newsUrl2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

// Execute both requests
$newsJson1 = curl_exec($ch1);
$newsJson2 = curl_exec($ch2);

// Close cURL sessions
curl_close($ch1);
curl_close($ch2);

// Decode JSON responses
$newsArray1 = json_decode($newsJson1, true);
$newsArray2 = json_decode($newsJson2, true);

// Combine articles from both sources
$combinedArticles = array_merge($newsArray1['articles'], $newsArray2['articles']);

// Display combined news articles
echo "<div class='news-container'>";
echo "<h1>Latest News</h1>";
foreach ($combinedArticles as $article) {
    echo "<div class='news-article'>";
    echo "<h2><a href='" . htmlspecialchars($article['url']) . "' target='_blank'>" . htmlspecialchars($article['title']) . "</a></h2>";
    echo "<p>" . htmlspecialchars($article['description']) . "</p>";
    echo "</div>";
}
echo "</div>";
?>

