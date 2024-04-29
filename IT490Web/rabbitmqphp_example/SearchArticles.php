<?php
$username = $_SESSION['username'];
$userSettings = fetchUserSettings($username);  // Ensure this function is implemented to fetch settings
$themeStylePath = '../routes/menuStyles.css';
if ($userSettings['has_dark_mode']) {
    $themeStylePath = 'css/darkModeStyles.css'; // Dark mode style
} elseif ($userSettings['has_alternative_theme']) {
    $themeStylePath = 'css/alternativeThemeStyles.css'; // Alternative theme style
}
if ($userSettings['has_custom_cursor']) {
    // Specify the path to the custom cursor image file
    $customCursorPath = "css/custom-cursor/sharingan-cursor.png";
    echo "<style>    body { cursor: url('$customCursorPath'), auto; }</style>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Articles</title>
    <link id="themeStyle" rel="stylesheet" href="<?php echo $themeStylePath; ?>" />
    <?php if ($userSettings['has_custom_cursor']): ?>
        <style>
            body {
                cursor: url('<?php echo $customCursorPath; ?>'), auto;
            }

        </style>
    <?php endif; ?>
</head>
<body>

    <?php require('nav.php'); ?>

    <form action="" method="GET">
        <input type="text" name="query" placeholder="Search for articles..." />
        <input type="submit" value="Search" />
    </form>

    <?php
    $servername = "10.147.17.233";
    $username = "IT490DB";
    $password = "IT490DB";
    $database = "EARLYBIRD";
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $searchQuery = isset($_GET['query']) ? $_GET['query'] : '';
    $apiKey = '898d8c1625884af1a9774e9662cb980d';
    $newsUrl = "https://newsapi.org/v2/everything?q=" . urlencode($searchQuery) . "&apiKey={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $newsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $newsJson = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "Curl error: " . curl_error($ch);
    }
    curl_close($ch);

    $newsArray = json_decode($newsJson, true);

    if ($newsArray && isset($newsArray['articles'])) {
        $articlesFromApi = $newsArray['articles'];
    } else {
        $articlesFromApi = [];
        echo "No articles found from the API or an error occurred.<br>";
    }

    if (!empty($searchQuery)) {
        $stmt = $conn->prepare("SELECT * FROM articles WHERE (title LIKE CONCAT('%', ?, '%') OR content LIKE CONCAT('%', ?, '%')) AND is_private = 0");
        $stmt->bind_param("ss", $searchQuery, $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT * FROM articles WHERE is_private = 0");
    }

    if ($result === false) {
        echo "Error executing query: " . $conn->error . "<br>";
    } else {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "ID: " . $row["id"] . " - Title: <a href='getArticleDetails.php?id=" . $row["id"] . "'>" . $row["title"] . "</a> - Content: " . substr($row["content"], 0, 100) . "..." . "<br>";
            }
        } else {
            echo "No articles found.<br>";
        }
    }

    foreach ($articlesFromApi as $article) {
        echo "Title: <a href='" . htmlspecialchars($article["url"]) . "'>" . htmlspecialchars($article["title"]) . "</a> - Description: " . htmlspecialchars($article["description"]) . "<br>";
    }

    $conn->close();
    ?>

</body>
</html>

