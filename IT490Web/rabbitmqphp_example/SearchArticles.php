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

    // Define API key and URL for news fetching
    $apiKey = '898d8c1625884af1a9774e9662cb980d';
    $newsUrl = "https://newsapi.org/v2/everything?q=" . urlencode($searchQuery) . "&apiKey={$apiKey}";

    // Initialize cURL session for news API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $newsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $newsJson = curl_exec($ch);
    curl_close($ch);

    // Decode JSON response from news API
    $newsArray = json_decode($newsJson, true);

    if ($newsArray && isset($newsArray['articles'])) {
        $articlesFromApi = $newsArray['articles'];
    } else {
        // Log error or handle it as appropriate
        $articlesFromApi = [];
        echo "No articles found from the API or an error occurred.<br>";
    }

    if (!empty($searchQuery)) {
        // Use prepared statements to prevent SQL injection and only select public articles
        $stmt = $conn->prepare("SELECT * FROM articles WHERE (title LIKE CONCAT('%', ?, '%') OR content LIKE CONCAT('%', ?, '%')) AND is_private = 0");
        $stmt->bind_param("ss", $searchQuery, $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Fetch all public articles if no search query is provided
        $result = $conn->query("SELECT * FROM articles WHERE is_private = 0");
    }

    if ($result === false) {
        echo "Error executing query: " . $conn->error . "<br>";
    } else {
        if ($result->num_rows > 0) {
            // Output the articles from the database
            while ($row = $result->fetch_assoc()) {
                echo "ID: " . $row["id"] . " - Title: <a href='getArticleDetails.php?id=" . $row["id"] . "'>" . $row["title"] . "</a> - Content: " . substr($row["content"], 0, 100) . "..." . "<br>";
            }
        } else {
            echo "No articles found.<br>";
        }
    }

    // Output the articles from the API
    foreach ($articlesFromApi as $article) {
        echo "Title: <a href='" . htmlspecialchars($article["url"]) . "'>" . htmlspecialchars($article["title"]) . "</a> - Description: " . htmlspecialchars($article["description"]) . "<br>";
    }

    // Close connection
    $conn->close();

    ?>

</body>
</html>

