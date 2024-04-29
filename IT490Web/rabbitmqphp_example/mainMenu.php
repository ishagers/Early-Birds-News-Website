<?php
require('session.php');
require('databaseFunctions.php');

checkLogin();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['username'];

$quests = fetchAvailableQuests($username);

// Fetch user preferences from the database or session
$userSettings = fetchUserSettings($username);  // Ensure this function is implemented to fetch settings
$articleData = fetchArticles(15, 'public', 'user');

$themeStylePath = '../routes/menuStyles.css';
if ($userSettings['has_dark_mode']) {
    $themeStylePath = 'css/futuristicStyles.css'; // Dark mode style
} elseif ($userSettings['has_alternative_theme']) {
    $themeStylePath = 'css/alternativeThemeStyles.css'; // Alternative theme style
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Main Menu</title>
    <!-- Corrected Stylesheet Link -->
    <link id="themeStyle" rel="stylesheet" href="<?php echo $themeStylePath; ?>" />
    <?php if ($userSettings['has_custom_cursor']): ?>
        <!-- Custom Cursor Style -->
        <link rel="stylesheet" href="css/custom-cursor.css" />
    <?php endif; ?>
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

