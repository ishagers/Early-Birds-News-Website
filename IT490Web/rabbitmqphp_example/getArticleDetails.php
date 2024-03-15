<?php

require('session.php');
require('databaseFunctions.php');
require_once('SQLPublish.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
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
    // Article title, content, and publication date display logic...

    // Ratings display logic...
    $averageRatingResponse = getAverageRatingByArticleId($articleId);
    if ($averageRatingResponse['status']) {
        echo "<div id='ratings'>";
        echo "<h3>Average Rating: " . htmlspecialchars($averageRatingResponse['averageRating']) . "</h3>";
        echo "</div>";
    } else {
        echo "<p>No ratings yet.</p>";
    }

        // Fetch and display comments
        $comments = getCommentsByArticleId($articleId);
        if ($comments['status']) {
            echo "<div id='comments'>";
            echo "<h3>Comments</h3>";
            if (!empty($comments['comments'])) {
                foreach ($comments['comments'] as $comment) {
                    echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment']) . "</p>";
                }
            } else {
                echo "<p>No comments yet.</p>";
            }
            echo "</div>";
        }

        echo "<div id='submit-comment'>";
        echo "<h3>Add a comment</h3>";
        echo "<form action='getArticleDetails.php?id=" . htmlspecialchars($articleId) . "' method='post'>";
        echo "<textarea name='comment' required></textarea>";
        echo "<button type='submit' name='submitComment'>Submit Comment</button>";
        echo "</form>";
        echo "</div>";

        // Optionally, display comments and other details here
    } else {
        echo "<p>Article not found.</p>";
    }
} else {
    echo "<p>No article ID provided.</p>";
}

?>
