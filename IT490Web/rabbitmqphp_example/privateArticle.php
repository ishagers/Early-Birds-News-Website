<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require('session.php');
require('databaseFunctions.php');
checkLogin();

$username = $_SESSION['username'];

// Adjusted to fetch articles for the logged-in user based on username
$userArticles = fetchUserArticles($username, 10, false); // False to include both private and public articles

// Assuming you have these functions correctly set up to handle the privacy toggle based on article ID and username.
if (isset($_POST['make_private']) || isset($_POST['make_public'])) {
    $articleId = $_POST['article_id'];
    $makePrivate = isset($_POST['make_private']);
    setArticlePrivacy($articleId, $username, $makePrivate);
    header('Location: ' . $_SERVER['PHP_SELF']); // Refresh the page to reflect changes
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Create Article</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>

    <?php require('nav.php'); ?>
</body>
</html>

<?php foreach ($userArticles as $article): ?>
    <div class="article">
        <h3><?php echo isset($article['title']) ? htmlspecialchars($article['title']) : 'No title'; ?></h3>
        <p><?php echo isset($article['content']) ? nl2br(htmlspecialchars($article['content'])) : 'No content'; ?></p>
        <?php if (isset($article['is_private']) && $article['is_private']): ?>
            <form method="post">
                <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($article['id']); ?>" />
                <button type="submit" name="make_public">Make Public</button>
            </form>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($article['id']); ?>" />
                <button type="submit" name="make_private">Make Private</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
