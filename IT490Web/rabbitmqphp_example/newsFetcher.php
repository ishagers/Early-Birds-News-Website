<?php

$apiKey = '898d8c1625884af1a9774e9662cb980d';
$newsUrl = "https://newsapi.org/v2/top-headlines?country=us&apiKey={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $newsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$newsJson = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);

if ($err) {
    echo "cURL Error: " . $err;
} else {
    $newsArray = json_decode($newsJson, true);
    
    if ($newsArray['status'] == 'ok') {
        echo "<div class='news-container'>";
        foreach ($newsArray['articles'] as $article) {
            echo "<div class='news-article'>";
            echo "<h2><a href='" . htmlspecialchars($article['url']) . "'>" . htmlspecialchars($article['title']) . "</a></h2>";
            echo "<p>" . htmlspecialchars($article['description']) . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "Failed to fetch news.";
    }
}
?>

