<?php
require('session.php');
require('databaseFunctions.php');
checkLogin();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debugging: Log session data
error_log("Session Data: " . print_r($_SESSION, true));

$username = $_SESSION['username'];

// Additional debugging: Confirm that username is set
if (!isset($username)) {
    error_log("Username is not set in the session.");
    die("Error: Session data is missing. Please log in again.");
}

$username = $_SESSION['username'];
$articleData = fetchArticles(15, 'public', 'user');
$quests = fetchAvailableQuests($username);
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
    <div class="content-container">
        <div class="mainMenu-container">
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

        <div class="quests-container">
            <h2>Available Quests</h2>
            <?php if (!empty($quests)): ?>
            <ul class="quests-list">
                <?php foreach ($quests as $quest): ?>
                <li class="quest-item">
                    <h3><?php echo htmlspecialchars($quest['name']); ?></h3>
                    <p><?php echo htmlspecialchars($quest['description']); ?></p>
                    <strong>Reward: <?php echo htmlspecialchars($quest['reward']); ?> EBP</strong>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p>No available quests at this moment. Check back later!</p>
            <?php endif; ?>
        </div>
    </div>
        <div class="logout-button">
            <a href="logout.php">Logout</a>
        </div>
</body>
</html>
