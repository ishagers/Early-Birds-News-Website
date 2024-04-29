<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: index.php');
    exit();
}

require('databaseFunctions.php');

$username = $_SESSION['username'];

try {
    $pdo = getDatabaseConnection(); // Using the getDatabaseConnection function

    $stmt = $pdo->prepare('SELECT a.id AS article_id, a.title, a.content, a.publication_date
                           FROM articles AS a
                           JOIN user_article_views uav ON a.id = uav.article_id
                           JOIN users u ON uav.user_id = u.id
                           WHERE u.username = :username
                           ORDER BY a.publication_date DESC');
    $stmt->execute(['username' => $username]);

    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Output the number of articles found
    echo "Articles found: " . count($articles);

} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

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
    echo "<style>body { cursor: url('$customCursorPath'), auto; }</style>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Article History</title>
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

    <h1>Article History</h1>
    <div class="article-history">
        <?php if (!empty($articles)): ?>
        <?php foreach ($articles as $article): ?>
        <div class="article">
            <h2><?= htmlspecialchars($article['title']) ?></h2>
            <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
            <small>Posted on: <?= htmlspecialchars($article['publication_date']) ?></small>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p>No articles found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

