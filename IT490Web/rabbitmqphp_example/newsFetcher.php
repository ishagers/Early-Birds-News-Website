<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Latest News</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .news-container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .news-article {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .news-article:last-child {
            border: none;
        }
        .news-article h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .news-article p {
            font-size: 16px;
        }
        .news-article a {
            text-decoration: none;
            color: #333;
        }
        .news-article a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

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
        echo "<h1>Latest News</h1>";
        foreach ($newsArray['articles'] as $article) {
            echo "<div class='news-article'>";
            echo "<h2><a href='" . htmlspecialchars($article['url']) . "' target='_blank'>" . htmlspecialchars($article['title']) . "</a></h2>";
            echo "<p>" . htmlspecialchars($article['description']) . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "Failed to fetch news.";
    }
}

?>

</body>
</html>

