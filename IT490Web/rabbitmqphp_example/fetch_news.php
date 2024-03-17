<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// The API endpoint with API key
$apiKey = 'UvENR8ucJtM7ZSpXxUokK3tttamiRut7HDaaXc6Q';
$newsUrl = "https://api.thenewsapi.com/v1/news/headlines?apiKey={$apiKey}&country=us&language=en";

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
// Print raw response for debugging
echo "<pre>Raw response:\n";
print_r($response);
echo "</pre>";

// Decode the JSON response
$responseData = json_decode($response, true);

// Check the response and proceed with your logic
if (isset($responseData['data']) && !empty($responseData['data']['articles'])) {
    foreach ($responseData['data']['articles'] as $article) {
        // Check if description is present; if not, use a placeholder or empty string
        $content = isset($article['description']) ? $article['description'] : 'No content available.';

        // Prepare SQL statement to insert the article into the database
        $stmt = $conn->prepare("INSERT INTO articles (title, content, author_id, is_private, publication_date, source, url) VALUES (?, ?, NULL, 0, NOW(), 'api', ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), source = VALUES(source), url = VALUES(url)");

        // Bind parameters
        $stmt->bind_param("sss", $article['title'], $content, $article['url']);

        // Execute the insert
        $stmt->execute();
    }
} else {
    echo "<p>Failed to fetch news.</p>";
}

// Close the database connection
$conn->close();
?>

