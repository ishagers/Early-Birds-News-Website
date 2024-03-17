<?php
require('session.php');
require('databaseFunctions.php');
require_once('SQLPublish.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

checkLogin();

$username = $_SESSION['username'];
$userId = null;
$article = null;

if (isset($_GET['id'])) {
    $articleId = $_GET['id'];

    try {
        $pdo = getDatabaseConnection();
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $userStmt->execute(['username' => $username]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];

            $checkViewStmt = $pdo->prepare("SELECT 1 FROM user_article_views WHERE user_id = :userId AND article_id = :articleId");
            $checkViewStmt->execute(['userId' => $userId, 'articleId' => $articleId]);
            $viewExists = $checkViewStmt->fetchColumn();

            if (!$viewExists) {
                $viewStmt = $pdo->prepare("INSERT INTO user_article_views (user_id, article_id) VALUES (:userId, :articleId)");
                $viewStmt->execute(['userId' => $userId, 'articleId' => $articleId]);
            }

            $_SESSION['viewed_articles'][$articleId] = true;

            $articleResponse = getArticleById($articleId);
            if ($articleResponse['status']) {
                $article = $articleResponse['article'];
            } else {
                echo "<p>Article not found.</p>";
                exit;
            }
        }
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }
} else {
    echo "<p>No article ID provided.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitComment'], $_POST['comment'])) {
    $commentContent = $_POST['comment'];
    $result = submitComment($articleId, $commentContent, $username);

    echo "<script>alert('" . htmlspecialchars($result['message']) . "'); window.location.href = 'mainMenu.php';</script>";
    exit();
}

// Assuming $article is defined by now
if (!empty($article)) {
    echo "<h2>" . htmlspecialchars($article['title']) . "</h2>";
    echo "<p>" . nl2br(htmlspecialchars($article['content'])) . "</p>";
    echo "<small>Published on: " . htmlspecialchars($article['publication_date']) . "</small>";

    $averageRatingResponse = getAverageRatingByArticleId($articleId);
    if ($averageRatingResponse['status']) {
        echo "<div id='ratings'>";
        echo "<h3>Average Rating: " . htmlspecialchars($averageRatingResponse['averageRating']) . "</h3>";
        echo "</div>";
    } else {
        echo "<p>No ratings yet.</p>";
    }

    $comments = getCommentsByArticleId($articleId);
    if ($comments['status']) {
        echo "<div id='comments'>";
        echo "<h3>Comments</h3>";
        foreach ($comments['comments'] as $comment) {
            echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . "</p>";
        }
        echo "</div>";
    } else {
        echo "<p>No comments yet.</p>";
    }

    echo "<div id='submit-comment'>";
    echo "<h3>Add a comment</h3>";
    echo "<form action='' method='POST'>";
    echo "<input type='hidden' name='articleId' value='" . htmlspecialchars($articleId) . "'>";
    echo "<textarea name='comment' required></textarea>";
    echo "<button type='submit' name='submitComment'>Submit Comment</button>";
    echo "</form>";
    echo "</div>";

    // Add more HTML for rating submission or other features as needed
}
?>
