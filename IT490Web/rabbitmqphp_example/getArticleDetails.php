<?php

require('session.php');
require('databaseFunctions.php');
require_once('SQLPublish.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

checkLogin();

$username = $_SESSION['username'];
$articleId = $userId = null;
$article = null;
$emailResponse = '';
$commentResponse = '';

// Check if an article ID is provided and fetch the article
if (isset($_GET['id'])) {
    $articleId = $_GET['id'];
    $article = getArticleById($articleId); // Assume this function returns the article array with a 'status' key
}

// Handle the share request
if (isset($_POST['submitShare']) && !empty($_POST['shareEmail']) && $article && $article['status']) {
    $recipientEmail = sanitizeInput($_POST['shareEmail']);
    $articleTitle = htmlspecialchars($article['article']['title']);
    $articleContent = nl2br(htmlspecialchars($article['article']['content']));
    $articleUrl = "";

    $emailResponse = SendArticle($recipientEmail, $articleTitle, $articleContent, $articleUrl);
}

// Handle comment submission
if (isset($_POST['submitComment']) && !empty($_POST['comment']) && $article && $article['status']) {
    $commentResponse = submitComment($articleId, sanitizeInput($_POST['comment']), $username);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Article Details - Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>

    <?php require('nav.php'); ?>

    <div class="main-container">
        <?php if ($article && $article['status']): ?>
            <div class="article-details">
                <h2><?php echo htmlspecialchars($article['article']['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($article['article']['content'])); ?></p>
                <small>Published on: <?php echo htmlspecialchars($article['article']['publication_date']); ?></small>

                <div id='share-article'>
                    <h3>Share this Article</h3>
                    <form action='getArticleDetails.php?id=<?php echo urlencode($articleId); ?>' method='post'>
                        <input type='email' name='shareEmail' placeholder='Enter email to share' required />
                        <button type='submit' name='submitShare'>Share Article</button>
                    </form>
                    <?php if (!empty($emailResponse)) {
                        echo "<p>$emailResponse</p>";
                    } ?>
                </div>

                <div id='comments'>
                    <h3>Comments</h3>
                    <?php $comments = getCommentsByArticleId($articleId); ?>
                    <?php if (!empty($comments['comments'])): ?>
                        <?php foreach ($comments['comments'] as $comment): ?>
                            <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong><?php echo htmlspecialchars($comment['comment']); ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No comments yet.</p>
                    <?php endif; ?>
                    <div id='submit-comment'>
                        <h3>Add a comment</h3>
                        <form action='getArticleDetails.php?id=<?php echo urlencode($articleId); ?>' method='post'>
                            <textarea name='comment' required></textarea>
                            <button type='submit' name='submitComment'>Submit Comment</button>
                        </form>
                        <?php if (!empty($commentResponse)) {
                            echo "<p>$commentResponse</p>";
                        } ?>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <p>Article not found.</p>
        <?php endif; ?>
    </div>

</body>
</html>
