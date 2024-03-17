<?php

// The API endpoint with API key
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

// Display the news
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

