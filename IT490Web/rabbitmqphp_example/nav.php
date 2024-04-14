<?php
require('databaseFunctions.php');
// Check if the session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the username is set in the session to customize the greeting
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$ebpPoints = isset($_SESSION['username']) ? fetchUserEBP($_SESSION['username']) : 0;

if (isset($_POST['add_ebp'])) {
    if (isset($_SESSION['username'])) {
        $currencyResponse = addCurrencyToUserByUsername($_SESSION['username'], 5);
        echo "<p>" . htmlspecialchars($currencyResponse['message']) . "</p>";
    } else {
        echo "<p>User must be logged in to receive currency.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Early Bird Articles</title>
    <!-- Link to the external CSS file -->
    <link rel="stylesheet" href="../routes/menuStyles.css">
</head>
<body>

<div class="header">
    <h1>Early Bird Articles</h1>
    <div class="user-info">
        Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
    </div>

    <form method="post" action="">
        <button type="submit" name="add_ebp">Add 5 EBP</button>
    </form>
    
</div>


<div class="nav-bar">
    <ul>
        <li><a href="writeArticle.php">Create Article</a></li>
        <li><a href="article-history.php">Article History</a></li>
        <li><a href="accountPreferences.php">Profile Preferences</a></li>
        <li><a href="privateArticle.php">Private</a></li>
        <li><a href="SearchArticles.php">SearchArticles</a></li>
        <li><a href="mainMenu.php">Home</a></li>
        <li><a href="NewsAPIData.php">Latest News</a></li>
        <li>
            <a href="store.php">
                <img src="./assets/EBP.png" alt="EBP Token" width="64" height="64" />
                <span id="ebpPoints"><?php echo $ebpPoints; ?></span>
            </a>
        </li>
    </ul>
</div>

<script>
    // Checks for changes in EBP points and update the display 
    setInterval(function() {
        // This assumes you have a route like "getEBP.php" that returns the current user's EBP points
        fetch('getEBP.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('ebpPoints').textContent = data;
            });
    }, 2000); // Update every 5 seconds, adjust as needed
</script>

</body>
</html>

