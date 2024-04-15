<?php
require 'session.php';
require 'nav.php';
checkLogin();

// Fetch friend list dynamically
$friendsList = fetchFriendsByUsername(getDatabaseConnection(), $_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Page</title>
    <link rel="stylesheet" href="path/to/styles.css">
    <script src="websocket.js"></script>
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

