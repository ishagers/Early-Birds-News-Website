<?php
require('session.php'); // Adjust the path as necessary
require('databaseFunctions.php');
checkLogin(); // Call the checkLogin function to ensure the user is logged in

if (isset($_POST['submitPreferences'])) {
    $selectedTopics = $_POST['topics'] ?? [];
    $username = $_SESSION['username']; // Ensure session username is correctly set

    // Update user preferences
    foreach ($selectedTopics as $topicId) {
        saveUserPreference($username, $topicId); // Directly use username
    }

    echo "<p>Preferences updated successfully!</p>";
    // Or redirect to another page
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Create Article</title>
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
            <li><a href="article-history.php">Article History</a></li>
            <li><a href="writeArticle.php">Create Article</a></li>
            <li><a href="mainMenu.php">Home</a></li>
        </ul>
    </div>
    <form action="profile.php" method="post">
        <div class="topics-selection">
            <h3>Select your topics of interest:</h3>
            <?php foreach ($topics as $topic): ?>
                <label>
                    <input type="checkbox" name="topics[]" value="<?php echo $topic['id']; ?>" />
                    <?php echo htmlspecialchars($topic['name']); ?>
                </label><br />
            <?php endforeach; ?>
        </div>
        <input type="submit" name="submitPreferences" value="Save Preferences" />
    </form> 

    <div class="logout-button">
            <a href="logout.php">Logout</a>
    </div>
</body>
</html>