<?php
require('session.php');
require('databaseFunctions.php');
checkLogin();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['username'];
$articleData = fetchUserArticles($username, 10, 'public');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Main Menu</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
    <!-- Include jQuery for AJAX calls -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.article-title').click(function () {
                var articleId = $(this).attr('data-article-id');
                console.log('Article ID clicked:', articleId);

                $.ajax({
                    url: 'getArticleDetails.php',
                    type: 'GET',
                    data: { 'id': articleId },
                        success: function (response) {
                    console.log("AJAX Response:", response);
                    $('#article-details').html(response);
                },
                    error: function () {
                        $('#article-details').html("<p>Error loading article.</p>");
                    }
                });
            });
        });
    </script>
</head>
<body>

    <?php require('nav.php'); ?>

    <div class="main-container">
        <!-- Article Titles -->
        <div class="articles-list">
            <?php if ($articleData['status']): ?>
                <?php foreach ($articleData['articles'] as $article): ?>
                    <div class="article">
                        <h3 class="article-title" data-article-id="<?php echo $article['id']; ?>">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </h3>
                        <small>Published on: <?php echo date('F j, Y, g:i a', strtotime($article['publication_date'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php echo $articleData['message']; ?></p>
            <?php endif; ?>
        </div>

        <!-- Article Details: Content, Comments, Ratings -->
        <div id="article-details" class="article-details">
            <!-- Article content, comments, and ratings will be loaded here -->
        </div>
    </div>

    <div class="logout-button">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
