<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

//Angel's GNews API key
$apikey = '96143db15e40e92b47eadab6d54b6255';
$query = 'example'; // Adjust your search query as needed
$url = "https://gnews.io/api/v4/search?q={$query}&lang=en&country=us&max=10&token={$apikey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if ($response === false) {
    echo "Curl error: " . curl_error($ch);
    curl_close($ch);
    exit;
}
$data = json_decode($response, true);
curl_close($ch);

// Check if articles key exists and is not empty
$articles = isset($data['articles']) ? $data['articles'] : [];

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

echo "<ul>"; // Start an unordered list for better web display

foreach ($articles as $article) {
    $title = $article['title'] ?? 'No title available.';
    $content = $article['content'] ?? 'No content available.';
    $articleUrl = $article['url'] ?? '#';
    $imageSrc = $article['image'] ?? 'path_to_default_image.jpg'; // Set a default image path if none is provided

    // Check if an article with the same title already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM articles WHERE title = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // If the article does not exist, insert it
    if ($count == 0) {
        $insertStmt = $conn->prepare("INSERT INTO articles (title, content, author_id, is_private, publication_date, source, url) VALUES (?, ?, NULL, 0, NOW(), 'api', ?)");
        $insertStmt->bind_param("sss", $title, $content, $articleUrl);
        $insertStmt->execute();
        $insertStmt->close();
    }

    // For a better web display, include hyperlinks and images
    echo "<li>";
    echo "<img src='" . htmlspecialchars($imageSrc) . "' alt='' style='width:100px; height:auto;'>";
    echo "<a href='" . htmlspecialchars($articleUrl) . "' target='_blank'>" . htmlspecialchars($title) . "</a>";
    echo "</li>";
}

echo "</ul>"; // Close the unordered list

// Close the database connection
$conn->close();

?>

