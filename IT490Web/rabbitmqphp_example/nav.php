<?php
// Check if the session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the username is set in the session to customize the greeting
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<div class="header">
    <h1>Early Bird Articles</h1>
    <div class="user-info">
        Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
    </div>
</div>
<div class="nav-bar">
    <ul>
        <li><a href="writeArticle.php">Create Article</a></li>
        <li><a href="article-history.php">Article History</a></li>
        <li><a href="accountPreferences.php">Profile Preferences</a></li>
        <li><a href="RatingAndPreference.php">RatingAndPreference</a></li>
        <li><a href="SearchArticles.php">SearchArticles</a></li>
        <li><a href="mainMenu.php">Home</a></li>
        <!-- Add or remove navigation items as needed -->
    </ul>
</div>
