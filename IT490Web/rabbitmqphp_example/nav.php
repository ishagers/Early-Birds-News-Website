<?php

require_once 'databaseFunctions.php';

// Check if the session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the username is set in the session to customize the greeting
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$ebpPoints = isset($_SESSION['username']) ? fetchUserEBP($_SESSION['username']) : 0;

// Handle adding EBP
if (isset($_POST['add_ebp'])) {
    if (isset($_SESSION['username'])) {
        $currencyResponse = addCurrencyToUserByUsername($_SESSION['username'], 5);
        echo "<p>" . htmlspecialchars($currencyResponse['message']) . "</p>";
    } else {
        echo "<p>User must be logged in to receive currency.</p>";
    }
}

// Include WebSocket script for live features like friend status updates
echo '<script src="websocket.js"></script>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css">
</head>
<body>
    <div class="header">
        <h1>Early Bird Articles</h1>
        <div class="user-info">
            Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
            <form method="post" action="">
                <button type="submit" name="add_ebp">Add 5 EBP</button>
            </form>
            <img src="../assets/EBP.png" alt="EB Points:" width="32" height="32" />
            <span class="eb-points-label">EB Points:</span>
            <span id="ebpPoints" class="eb-points"><?php echo $ebpPoints; ?></span>
        </div>
    </div>

    <div class="nav-bar">
        <ul>
            <li><a href="writeArticle.php">Create Article</a></li>
            <li><a href="article-history.php">Article History</a></li>
            <li><a href="accountPreferences.php">Profile Preferences</a></li>
            <li><a href="privateArticle.php">Private</a></li>
            <li><a href="SearchArticles.php">Search Articles</a></li>
            <li><a href="mainMenu.php">Home</a></li>
            <li><a href="NewsAPIData.php">Latest News</a></li>
            <li><a href="store.php">Store</a></li>
            <li><a href="chatLayout.php">Chat</a></li> <!-- Direct link to the chat page -->
        </ul>
    </div>

    <script>
        // Periodically check for changes in EBP points and update the display
        setInterval(function() {
            fetch('getEBP.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('ebpPoints').textContent = data;
                });
        }, 1000); // Update every second
    </script>
</body>
</html>

