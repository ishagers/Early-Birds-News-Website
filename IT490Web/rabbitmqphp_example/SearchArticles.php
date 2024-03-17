<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Search Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>

    <?php require('nav.php'); ?>
    <form action="" method="GET">
        <input type="text" name="query" placeholder="Search for articles..." />
        <input type="submit" value="Search" />
    </form>

    <?php

echo "Testing the Search Article.<br>";

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

// Define API keys and URLs for news fetching
$apiKey = 'UvENR8ucJtM7ZSpXxUokK3tttamiRut7HDaaXc6Q'; // Decide which api key we will use, same thing for the newsURL. 


// Might need to have several urls so that the users preferences decide which url they will prioritize 
$newsUrl = "https://newsapi.org/v2/everything?q=".urlencode($searchQuery)."&apiKey={$apiKey}";

// Initialize cURL session for news API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $newsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$newsJson = curl_exec($ch);
curl_close($ch);

// Decode JSON response from news API
$newsArray = json_decode($newsJson, true);
$articlesFromApi = $newsArray['articles'];

if (!empty($searchQuery)) {
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
        // Output the articles from the database
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row["id"]. " - Title: <a href='articleDetails.php?id=" . $row["id"] . "'>" . $row["title"]. "</a> - Content: " . substr($row["content"], 0, 100). "..." . "<br>";
        }
    } else {
        echo "No articles found.<br>";
    }
}

// Output the articles from the API
foreach ($articlesFromApi as $article) {
    echo "Title: <a href='" . $article["url"] . "'>" . $article["title"]. "</a> - Description: " . $article["description"] . "<br>";
}

// Close connection
$conn->close();

    ?>

</body>
</html>

