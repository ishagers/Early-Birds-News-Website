<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Latest News</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .news-container {
            max-width: 800px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .news-article {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .news-article:last-child {
            border: none;
        }
        .news-article h2 {
            font-size: 20px;
            margin: 0 0 10px;
        }
        .news-article p {
            font-size: 16px;
        }
        .news-article a {
            display: inline-block;
            margin-top: 10px;
            color: #333;
            text-decoration: none;
        }
        .news-article a:hover {
            text-decoration: underline;
        }
        .news-image {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

<div class="news-container">
    <h1>Latest News</h1>
    <?php

    // Your PHP script here

    // The API endpoint with your API key
    $url = "https://newsapi.org/v2/top-headlines?country=us&category=business&apiKey=898d8c1625884af1a9774e9662cb980d";

    // Initialize a cURL session
    $curl = curl_init($url);

    // Set options for the cURL session
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: EarlyBirds/Beta' // Updated User-Agent header
    ]);

    // Execute the cURL session and get the response
    $response = curl_exec($curl);

    // Close the cURL session
    curl_close($curl);

    // Decode the JSON response
    $responseData = json_decode($response, true);

    // Check if response is OK and articles are available
    if ($responseData['status'] == 'ok' && !empty($responseData['articles'])) {
        foreach ($responseData['articles'] as $article) {
            echo "<div class='news-article'>";
            echo "<h2><a href='" . htmlspecialchars($article['url']) . "' target='_blank'>" . htmlspecialchars($article['title']) . "</a></h2>";
            if (!empty($article['urlToImage'])) {
                echo "<img src='" . htmlspecialchars($article['urlToImage']) . "' alt='' class='news-image'>";
            }
            echo "<p>" . htmlspecialchars($article['description']) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Failed to fetch news.</p>";
    }

    ?>
</div>

</body>
</html>

