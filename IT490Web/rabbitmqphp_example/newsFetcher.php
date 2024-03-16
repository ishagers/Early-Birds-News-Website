<?php

$apiKey = '898d8c1625884af1a9774e9662cb980d';
$newsUrl = "https://newsapi.org/v2/top-headlines?country=us&apiKey={$apiKey}";
// Fetch the news data
$newsJson = file_get_contents($newsUrl);
$newsArray = json_decode($newsJson, true);

// Check if the fetch was successful
if ($newsArray['status'] == 'ok') {
    // Display the news articles
    foreach ($newsArray['articles'] as $article) {
        echo "<div class='news-item'>";
        echo "<h3><a href='{$article['url']}' target='_blank'>{$article['title']}</a></h3>";
        echo "<p>{$article['description']}</p>";
        echo "</div>";
    }
} else {
    echo "Failed to fetch news.";
}
?>

