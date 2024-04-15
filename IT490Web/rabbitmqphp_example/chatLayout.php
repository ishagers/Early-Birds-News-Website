<?php
require 'session.php';  // Handle sessions
require 'nav.php';      // Include navigation bar

checkLogin(); // Ensure the user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Page</title>
    <link rel="stylesheet" href="path/to/styles.css">
    <script src="websocket.js"></script> <!-- WebSocket logic for chat -->
</head>
<body>
    <!-- Chat Widget -->
    <div id="chat-widget">
        <div id="messages"></div>
        <input type="text" id="messageInput" placeholder="Type a message...">
        <button onclick="sendMessage()">Send</button>
    </div>

    <!-- Main content of the page -->
    <main>
        <!-- Content specific to this page -->
    </main>

    <!-- Include any additional scripts needed for this page -->
    <script src="loadFriends.js"></script>
</body>
</html>

