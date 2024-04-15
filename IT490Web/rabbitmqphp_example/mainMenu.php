<?php
require('session.php');
require('databaseFunctions.php');
checkLogin();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['username'];
$articleData = fetchArticles(15, 'public', 'user');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Main Menu</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>

    <?php require('nav.php'); ?>

    <div class="mainMenu-container">
        <!-- Article Titles -->
        <div class="articles-list">
            <?php if ($articleData['status']): ?>
                <?php foreach ($articleData['articles'] as $article): ?>
                    <div class="article">
                        <!-- Wrap the article title in an anchor tag -->
                        <h3>
                            <a href="getArticleDetails.php?id=<?php echo urlencode($article['id']); ?>">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <small>Published on: <?php echo date('F j, Y, g:i a', strtotime($article['publication_date'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php echo $articleData['message']; ?></p>
            <?php endif; ?>
        </div>

    </div>

    <div class="logout-button">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
