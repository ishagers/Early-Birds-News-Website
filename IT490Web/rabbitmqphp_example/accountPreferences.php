<?php

require('session.php');
require('databaseFunctions.php');

checkLogin();

// Fetch all necessary data
$topics = fetchAllTopics();
$currentPreferences = fetchUserPreferences($_SESSION['username']);
$friendsList = fetchFriendsByUsername(getDatabaseConnection(), $_SESSION['username']);
$usernames = fetchAllUsernames($_SESSION['username']); // Fetching all other usernames
$receivedRequests = fetchReceivedFriendRequests(getDatabaseConnection(), $_SESSION['username']);

// Debugging output to see what's being returned by fetchFriendsByUsername()
echo '<pre>'; print_r($friendsList); echo '</pre>'; 

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
if (isset($_GET['friendsUpdated'])) {
    $friendsList = fetchFriendsByUsername(getDatabaseConnection(), $_SESSION['username']); // Refetch the friends list
}
	// Display any session messages if they exist
if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']); // Clear the message after displaying
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

    <!-- Display Received Friend Requests -->
    <div class="friend-requests">
        <h3>Received Friend Requests:</h3>
        <ul>
            <?php foreach ($receivedRequests as $request): ?>
                <li>
                    <?php
                    // Ensure username is not null before displaying
                    $requesterUsername = htmlspecialchars($request['username'] ?? 'Unknown');
                    $requesterId = htmlspecialchars($request['user_id1'] ?? '0'); // Provide a fallback ID or handle this case
                    ?>
                    <?php echo $requesterUsername; ?>
                    <form action="respondToRequest.php" method="post" style="display: inline;">
                        <input type="hidden" name="requester_id" value="<?php echo $requesterId; ?>">
                        <button type="submit" name="response" value="accept">Accept</button>
                        <button type="submit" name="response" value="reject">Reject</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Display Friends List -->
	<div class="friends-list">
	    <h3>Your Friends:</h3>
	    <ul>
		<?php foreach ($friendsList as $friend): ?>
		    <li>
		        <?php
		        // Use htmlspecialchars to prevent XSS and handle null values
		        $friendName = htmlspecialchars($friend['username'] ?? 'Unknown');
		        $friendStatus = htmlspecialchars($friend['status'] ?? 'No status');
		        ?>
		        <?php echo "{$friendName} - {$friendStatus}"; ?>
		    </li>
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

