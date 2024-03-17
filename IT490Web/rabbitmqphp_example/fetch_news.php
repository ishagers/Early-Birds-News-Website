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
        // Prepare SQL statement to insert the article into the database
        $stmt = $conn->prepare("INSERT INTO articles (title, content, author_id, is_private, publication_date, source, url) VALUES (?, ?, NULL, 0, NOW(), 'api', ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), source = VALUES(source), url = VALUES(url)");

        // Bind parameters
        $stmt->bind_param("sss", $article['title'], $article['description'], $article['url']);

        // Execute the insert
        $stmt->execute();
    }
} else {
    echo "<p>Failed to fetch news.</p>";
}

// Close the database connection
$conn->close();
?>

