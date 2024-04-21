<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Article Details - Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>

    <?php
    require('nav.php');
    require('session.php');
    require('databaseFunctions.php');

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    checkLogin();

    $username = $_SESSION['username'];
    $articleId = isset($_GET['id']) ? $_GET['id'] : null;
    $article = $articleId ? getArticleById($articleId) : null;

    // Check the source of the article
    $isFromApi = $article && isset($article['source']) && $article['source'] === 'API';

    if ($_POST) {
        if (isset($_POST['submitShare']) && !empty($_POST['shareEmail']) && $article) {
            $emailResponse = sendArticle($_POST['shareEmail'], $article['title'], $article['content'], $article['url']);
        }

        if (isset($_POST['submitComment']) && !empty($_POST['comment']) && $article) {
            $commentResponse = submitComment($articleId, $_POST['comment'], $username);
        }
    }
    ?>

    <div class="article-details-container">
        <div class="main-container">
            <?php if ($article): ?>
            <div class="article-details">
                <h2><?= htmlspecialchars($article['title']) ?></h2>
                <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
                <small>Author: <?= htmlspecialchars($article['author']) ?> <?= $isFromApi ? '(API)' : '(Local)' ?></small><br />
                <small>Published on: <?= htmlspecialchars($article['publication_date']) ?></small>

                <!-- Display and handle share functionality -->
                <div id='share-article'>
                    <h3>Share this Article</h3>
                    <form method='post'>
                        <input type='email' name='shareEmail' placeholder='Enter email to share' required />
                        <button type='submit' name='submitShare'>Share Article</button>
                    </form>
                    <?= !empty($emailResponse) ? "<p>$emailResponse</p>" : "" ?>
                </div>

                <!-- Comments and rating sections omitted for brevity, follow similar structure -->

            </div>
            <?php else: ?>
            <p>Article not found or error occurred.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
