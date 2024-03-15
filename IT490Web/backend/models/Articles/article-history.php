<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: index.php');
    exit();
}

// Assuming you're using databaseFunctions.php for database operations
require_once('../../../rabbitmqphp_example/databaseFunctions.php');


$username = $_SESSION['username'];

try {
    $pdo = getDatabaseConnection(); // Using the getDatabaseConnection function

    // Replace 'user_article_views' with your actual table name for tracking views
    // Adjust the SELECT query based on your actual database schema
    $stmt = $pdo->prepare('SELECT a.article_id, a.title, a.content, a.date_posted FROM articles a JOIN user_article_views uav ON a.article_id = uav.article_id WHERE uav.username = :username ORDER BY a.date_posted DESC');
    $stmt->execute(['username' => $username]);

    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Article History</title>
    <!-- Adjusted path to the styles.css file to go one level up -->
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Article History</h1>
    <div class="article-history">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $article): ?>
                <div class="article">
                    <h2><?= htmlspecialchars($article['title']) ?></h2>
                    <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
                    <small>Posted on: <?= htmlspecialchars($article['date_posted']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No articles found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

