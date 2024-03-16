<?php
// Start the session and include necessary files
session_start();
require('path/to/databaseFunctions.php');
require('path/to/yourAuthenticationFile.php'); // This file should contain your checkLogin() function

// Check if the user is logged in
checkLogin();

// Handle the privacy toggle request if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId = $_POST['article_id'] ?? null;
    $makePrivate = isset($_POST['make_private']);

    // Call your function to toggle the article's privacy
    $privacyResult = toggleArticlePrivacy($articleId, $makePrivate);

    // You can store a message in the session to show after redirecting
    $_SESSION['message'] = $privacyResult['message'];

    // Redirect to avoid form resubmission issues
    header('Location: userArticles.php');
    exit();
}

// Retrieve the logged-in user's username from the session
$username = $_SESSION['username'];

// Fetch all articles for the logged-in user
$userArticles = fetchUserArticles($username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Your Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>

    <?php require('nav.php'); ?>

    <!-- Display a session message if set -->
    <?php if (isset($_SESSION['message'])): ?>
    <p><?php echo $_SESSION['message']; ?></p>
    <?php unset($_SESSION['message']); // Clear the message after displaying ?>
    <?php endif; ?>

    <div class="articles-container">
        <?php foreach ($userArticles as $article): ?>
        <div class="article">
            <h3><?php echo htmlspecialchars($article['title']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
            <form method="post">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>" />
                <?php if ($article['is_private']): ?>
                <button type="submit" name="make_public">Make Public</button>
                <?php else: ?>
                <button type="submit" name="make_private">Make Private</button>
                <?php endif; ?>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Add more of your HTML as needed -->

</body>
</html>
