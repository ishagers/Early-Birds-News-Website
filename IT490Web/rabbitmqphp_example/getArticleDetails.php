<?php

require('session.php');
require('databaseFunctions.php');
require_once('SQLPublish.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

checkLogin();

$username = $_SESSION['username'];
$articleId = $userId = null;

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];

    try {
        $pdo = getDatabaseConnection(); 
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $userStmt->execute(['username' => $username]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];

            if (!isset($_SESSION['viewed_articles']) || !in_array($articleId, $_SESSION['viewed_articles'])) {
                $viewStmt = $pdo->prepare("INSERT INTO user_article_views (user_id, article_id) VALUES (:userId, :articleId)");
                $viewStmt->execute(['userId' => $userId, 'articleId' => $articleId]);
                $_SESSION['viewed_articles'][] = $articleId;
            }

            $article = getArticleById($articleId, $pdo);
        }
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }
}

if (isset($_POST['submitComment']) && !empty($_POST['comment']) && isset($articleId)) {
    $commentContent = $_POST['comment'];
    $username = $_SESSION['username']; 

    $queryValues = array();
    $queryValues['type'] = 'create_comment';
    $queryValues['articleId'] = $articleId;
    $queryValues['content'] = $commentContent;
    $queryValues['username'] = $username;

    $result = publisher($queryValues);

    echo '<pre>';
    var_dump($result);
    echo '</pre>';

    if (isset($result['returnCode']) && $result['returnCode'] === "0") {
        echo "<script>alert('Comment successfully made'); window.location.href = 'mainMenu.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error submitting comment'); window.location.href='mainMenu.php';</script>";
        exit();
    }
}

if ($article && $article['status']) {
    echo "<h2>" . htmlspecialchars($article['article']['title']) . "</h2>";
    echo "<p>" . nl2br(htmlspecialchars($article['article']['content'])) . "</p>";
    echo "<small>Published on: " . htmlspecialchars($article['article']['publication_date']) . "</small>";

    $averageRatingResponse = getAverageRatingByArticleId($articleId);
    // Rating and comments display logic...
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Article Details</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Article details and comments HTML goes here -->
</body>
</html>

