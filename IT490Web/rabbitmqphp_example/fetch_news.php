<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your GNews API key
$apikey = '96143db15e40e92b47eadab6d54b6255';
$query = 'example'; // This is your search query, adjust as needed
$url = "https://gnews.io/api/v4/search?q={$query}&lang=en&country=us&max=10&token={$apikey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = json_decode(curl_exec($ch), true);
curl_close($ch);
$articles = $data['articles'];

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

foreach ($articles as $article) {
    // Check if content is present; if not, use 'No content available.'
    $content = isset($article['content']) ? $article['content'] : 'No content available.';
    // Prepare SQL statement to insert the article into the database
    $stmt = $conn->prepare("INSERT INTO articles (title, content, author_id, is_private, publication_date, source, url) VALUES (?, ?, NULL, 0, NOW(), 'api', ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), source = VALUES(source), url = VALUES(url)");
    
    // Bind parameters
    $stmt->bind_param("sss", $article['title'], $content, $article['url']);
    
    // Execute the insert
    $stmt->execute();
    
    echo "Inserted: " . $article['title'] . "\n"; // For debugging
}

// Close the database connection
$conn->close();
?>

