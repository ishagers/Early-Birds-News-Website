<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require('session.php'); // Adjust the path as necessary
require('databaseFunctions.php');

checkLogin(); 

// Fetch topics from database
$topics = fetchAllTopics();
$currentPreferences = fetchUserPreferences($_SESSION['username']);
$friendsList = fetchFriendsByUsername(getDatabaseConnection(), $_SESSION['username']); // Fetching friends list by username

if (isset($_POST['submitPreferences'])) {
    $selectedTopics = $_POST['topics'] ?? [];
    $username = $_SESSION['username']; // Ensure session username is correctly set

    // Update user preferences
    foreach ($selectedTopics as $topicId) {
        saveUserPreference($username, $topicId); // Function to save preference
    }

    // Redirect to the same page to refresh the content
    header('Location: accountPreferences.php');
    exit;
}

if (isset($_POST['clearPreferences'])) {
    // Clear user preferences
    $message = clearUserPreferences($_SESSION['username']);

    // Redirect to the same page to refresh the content
    header('Location: accountPreferences.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - User Settings</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>
    <?php require('nav.php'); ?>
    <h2>Profile Settings</h2>

    <!-- Display Friends List -->
    <div class="friends-list">
        <h3>Your Friends:</h3>
        <ul>
            <?php foreach ($friendsList as $friend): ?>
                <li><?php echo htmlspecialchars($friend['username']); ?> - <?php echo htmlspecialchars($friend['status']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <form action="accountPreferences.php" method="post">
        <div class="topics-selection">
            <h3>Select your topics of interest:</h3>
            <?php foreach ($topics as $topic): ?>
                <label>
                    <input type="checkbox" name="topics[]" value="<?php echo $topic['id']; ?>" <?php echo in_array($topic['id'], $currentPreferences) ? 'checked' : ''; ?> />
                    <?php echo htmlspecialchars($topic['name']); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="form-buttons">
            <input type="submit" name="submitPreferences" value="Save Preferences" />
            <input type="submit" name="clearPreferences" value="Clear Preferences" />
        </div>
    </form>

    <div class="logout-button">
        <a href="logout.php">Logout</a>
    </div>

</body>
</html>

