<?php
require('session.php'); // Adjust the path as necessary
checkLogin(); // Call the checkLogin function to ensure the user is logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Main Menu</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
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
    <div class="logout-button">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
