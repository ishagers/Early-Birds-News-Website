<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: index.php');
    exit();
}

// Assuming you're using databaseFunctions.php for database operations
require_once('databaseFunctions.php');

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Article History</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
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

