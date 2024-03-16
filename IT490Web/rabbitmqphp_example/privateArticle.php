<?php
require('session.php');
require('databaseFunctions.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
checkLogin();

$username = $_SESSION['username'];

// Retrieve the user's articles including both private and public
$userArticles = fetchUserArticles($username,10, true); // Assuming this function fetches articles based on username and privacy flag

// Toggle the privacy of the article if requested
if (isset($_POST['toggle_privacy'])) {
    $articleId = $_POST['article_id'];
    $currentPrivacy = $_POST['current_privacy'];
    setArticlePrivacy($articleId, $username, !$currentPrivacy); // Toggle the privacy state
    header('Location: ' . $_SERVER['PHP_SELF']); // Refresh the page to reflect changes
    exit();
}

// Display article details if an ID is provided
$articleDetails = null;
if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    $articleDetails = getArticleById($articleId); // Use getArticleById from databaseFunctions.php
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Private Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>

    <?php require('nav.php'); ?>

    <?php foreach ($userArticles as $article) {
        // Check if $article is an array and has all the expected keys before trying to access them
        if (is_array($article) && isset($article['title'], $article['content'], $article['id'], $article['is_private'])) {
            echo "<div class='article'>";
            echo "<h3>" . htmlspecialchars($article['title']) . "</h3>";
            echo "<p>" . nl2br(htmlspecialchars($article['content'])) . "</p>";
            // The rest of your code for displaying the article and the form
        } else {
            // Handle the case where $article is not as expected
            echo "<p>Error: Article data is not available.</p>";
        }
    } ?>

</body>
</html>
