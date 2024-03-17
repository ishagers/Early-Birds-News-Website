<?php

// Database connection details
$servername = "10.147.17.233";
$username = "IT490DB";
$password = "IT490DB";
$database = "EARLYBIRD";

// Establish MySQL connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// The API endpoint with your API key
$apiKey = '898d8c1625884af1a9774e9662cb980d';
$newsUrl = "https://newsapi.org/v2/top-headlines?country=us&category=business&apiKey={$apiKey}";

// Initialize cURL session
$curl = curl_init($newsUrl);

// Set options for the cURL session
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: EarlyBirds/Beta'
]);

// Execute the cURL session and get the response
$response = curl_exec($curl);

// Close the cURL session
curl_close($curl);

// Decode the JSON response
$responseData = json_decode($response, true);

// Display and insert the news articles
if ($responseData['status'] == 'ok' && !empty($responseData['articles'])) {
    foreach ($responseData['articles'] as $article) {
        // Display the article
        echo "<div class='news-article'>";
        echo "<h2><a href='" . htmlspecialchars($article['url']) . "' target='_blank'>" . htmlspecialchars($article['title']) . "</a></h2>";
        if (!empty($article['urlToImage'])) {
            echo "<img src='" . htmlspecialchars($article['urlToImage']) . "' alt='' class='news-image'>";
        }
        echo "<p>" . htmlspecialchars($article['description']) . "</p>";
        echo "</div>";

        // Prepare SQL statement to insert the article into the database
        // Note: Change to your adjusted schema and fields accordingly
        $stmt = $conn->prepare("INSERT INTO articles (title, content, is_api_article, api_source, api_url, api_image_url) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), api_source = VALUES(api_source), api_url = VALUES(api_url), api_image_url = VALUES(api_image_url)");
        $apiSource = 'NewsAPI'; // Example source name
        $isApiArticle = 1; // Flag indicating the article is from an API
        $stmt->bind_param("ssisss", $article['title'], $article['description'], $isApiArticle, $apiSource, $article['url'], $article['urlToImage']);
        
        // Execute the insert
        $stmt->execute();
    }
} else {
    echo "<p>Failed to fetch news.</p>";
}

// Close the database connection
$conn->close();
?>

