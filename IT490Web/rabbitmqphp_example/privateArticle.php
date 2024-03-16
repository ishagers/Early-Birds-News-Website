<?php
require('session.php');
require('databaseFunctions.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
checkLogin();

$username = $_SESSION['username'];

// Retrieve the user's articles including both private and public
$userArticles = fetchUserArticles($username, true); // Assuming this function fetches articles based on username and privacy flag

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

    <?php foreach ($userArticles as $article): ?>
    <div class="article">
        <h3><?php echo htmlspecialchars($article['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>" />
            <input type="hidden" name="current_privacy" value="<?php echo $article['is_private']; ?>" />
            <button type="submit" name="toggle_privacy">
                <?php echo $article['is_private'] ? 'Make Public' : 'Make Private'; ?>
            </button>
        </form>
        <?php if ($articleDetails && $article['id'] == $articleDetails['article']['id']): ?>
        <!-- Display the selected article details here -->
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

</body>
</html>
