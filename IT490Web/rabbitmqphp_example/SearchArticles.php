<?php
echo "Script started.<br>";

// MySQL connection parameters
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
echo "Connected to database successfully.<br>";

// Handle search queries or fetch all articles if no query is specified
$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';

if (!empty($searchQuery)) {
    // Basic search functionality for demonstration purposes
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM articles WHERE title LIKE CONCAT('%', ?, '%') OR content LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("ss", $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all articles if no search query is provided
    $result = $conn->query("SELECT * FROM articles");
}

if ($result === false) {
    echo "Error executing query: " . $conn->error . "<br>";
} else {
    if ($result->num_rows > 0) {
        // Output the articles
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row["id"]. " - Title: " . $row["title"]. " - Content: " . substr($row["content"], 0, 100). "<br>";
        }
    } else {
        echo "No articles found.<br>";
    }
}

// Close connection
$conn->close();
?>
