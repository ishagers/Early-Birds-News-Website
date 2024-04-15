<?php

require 'session.php';  // Handle sessions
require 'nav.php';      // Include navigation bar

checkLogin(); // Ensure the user is logged in

// Fetch friend list dynamically
$friendsList = fetchFriendsByUsername(getDatabaseConnection(), $_SESSION['username']);

// Assume the token is stored in $_SESSION['token'] when the user logs in
$token = $_SESSION['token'] ?? 'no-token'; // Ensure you have a fallback or handle cases where the token is not set

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Page</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Updated path to styles.css -->
    <script>
        // This makes the session token available to the WebSocket script
        var token = "<?php echo $token; ?>";
    </script>
    <script src="websocket.js"></script> <!-- Include WebSocket logic for chat -->
</head>
<body>
    <!-- Chat Widget -->
    <div id="chat-widget">
        <div id="friends-list">
            <?php foreach ($friendsList as $friend): ?>
                <div onclick="startChatWith('<?= htmlspecialchars($friend['username']) ?>')">
                    <?= htmlspecialchars($friend['username']) ?>
                    <!-- Display online/offline status -->
                </div>
            <?php endforeach; ?>
        </div>
        <div id="messages"></div>
        <input type="text" id="messageInput" placeholder="Type a message...">
        <button onclick="sendMessage()">Send</button>
    </div>

    <main>
        <!-- Main content of the page -->
    </main>

    <script src="loadFriends.js"></script>
</body>
</html>

