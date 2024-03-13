<?php
require('session.php');
require('databaseFunctions.php');
checkLogin();

$articleData = fetchRecentArticles(3);
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
            // Event listener for each article title
            $('.article-title').on('click', function () {
                var articleId = $(this).data('article-id');
                console.log('Article ID clicked:', articleId); // Debug line to check the captured article ID

                // AJAX request to get the article details
                $.ajax({
                    url: 'getArticleDetails.php',
                    type: 'GET',
                    data: { 'id': articleId },
                    success: function (response) {
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
    <div class="header">
        <h1>Early Bird Articles</h1>
        <div class="user-info">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </div>
    </div>
    <div class="nav-bar">
        <ul>
            <li><a href="writeArticle.php">Create Article</a></li>
            <li><a href="article-history.php">Article History</a></li>
            <li><a href="keyword-settings.php">Keyword Settings</a></li>
            <li><a href="account-settings.php">Account Settings</a></li>
        </ul>
    </div>

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

    <div class="logout-button">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
