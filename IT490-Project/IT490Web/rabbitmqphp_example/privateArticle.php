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

    // Fetch all articles (both private and public) for the logged-in user
    // You could adjust the query to exclude API sourced articles by adding AND source = 'user'
    $stmt = $pdo->prepare('SELECT id, title, content, is_private, publication_date, source
                           FROM articles
                           WHERE author_id = (SELECT id FROM users WHERE username = :username)
                           ORDER BY publication_date DESC');
    $stmt->execute(['username' => $username]);

    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Toggle privacy if the request comes from a form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $articleId = $_POST['article_id'] ?? null;
        if (isset($_POST['make_private'])) {
            setArticlePrivate($articleId, $username); // Implement this function to set the article as private
        } elseif (isset($_POST['make_public'])) {
            setArticlePublic($articleId, $username); // Implement this function to set the article as public
        }
        header('Location: ' . $_SERVER['PHP_SELF']); // Refresh the page to reflect changes
        exit();
    }

} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

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

    <h1>Your Articles</h1>
    <div class="articles-container">
        <?php foreach ($articles as $article): ?>
            <div class="article">
                <h2><?= htmlspecialchars($article['title']) ?></h2>
                <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
                <small>Published on: <?= htmlspecialchars($article['publication_date']) ?></small>
                <!-- Consider showing the source of the article or excluding API sourced articles from these operations -->
                <?php if ($article['source'] == 'user'): ?>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>" />
                        <?php if ($article['is_private']): ?>
                            <button type="submit" name="make_public">Make Public</button>
                        <?php else: ?>
                            <button type="submit" name="make_private">Make Private</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

