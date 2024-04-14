<?php

require('session.php');
require('databaseFunctions.php');

checkLogin();

// Fetch all necessary data
$topics = fetchAllTopics();
$currentPreferences = fetchUserPreferences($_SESSION['username']);
$friendsList = fetchFriendsByUsername(getDatabaseConnection(), $_SESSION['username']);
$usernames = fetchAllUsernames($_SESSION['username']); // Fetching all other usernames

if (isset($_POST['submitPreferences'])) {
    $selectedTopics = $_POST['topics'] ?? [];
    $username = $_SESSION['username'];

    foreach ($selectedTopics as $topicId) {
        saveUserPreference($username, $topicId);
    }

    header('Location: accountPreferences.php');
    exit;
}

if (isset($_POST['clearPreferences'])) {
    clearUserPreferences($_SESSION['username']);
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

	<!-- Display Other Usernames -->
	<div class="other-users">
	    <h3>Other Users:</h3>
	    <ul>
		<?php foreach ($usernames as $user): ?>
		    <li>
		        <?php echo htmlspecialchars($user['username']); ?>
		        <!-- Form to send friend request -->
		        <form action="sendFriendRequest.php" method="post" style="display: inline;">
		            <input type="hidden" name="friend_username" value="<?php echo htmlspecialchars($user['username']); ?>">
		            <button type="submit">Send Friend Request</button>
		        </form>
		    </li>
		<?php endforeach; ?>
	    </ul>
	</div>

    <!-- User Preferences Form -->
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

