<?php

require_once 'databaseFunctions.php';

// Check if the session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the username is set in the session to customize the greeting
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$ebpPoints = isset($_SESSION['username']) ? fetchUserEBP($_SESSION['username']) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat - Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .chat-widget, .public-chat-widget {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            padding: 10px;
        }
        .public-chat-widget {
            position: relative;
            width: 80%;
            margin: 20px auto;
            height: 400px;
            overflow: hidden;
        }
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            height: 400px;
            overflow: hidden;
            z-index: 1000;
        }
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 10px;
        }
        .chat-input {
            width: calc(100% - 20px);
            margin-bottom: 5px;
        }
        button {
            align-self: center;
        }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="header">
    <h1>Chat with Early Bird Community</h1>
    <div class="user-info">
        Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
        <span class="eb-points-label">EB Points:</span>
        <span class="eb-points"><?php echo $ebpPoints; ?></span>
    </div>
</div>

<!-- Public Chat Section -->
<div class="public-chat-widget">
    <h2>Public Chat</h2>
    <div id="publicChatBox" class="chat-messages"></div>
    <textarea id="publicMessage" class="chat-input" placeholder="Type your message here..."></textarea>
    <button onclick="sendPublicMessage()">Send</button>
</div>

<!-- Private Chat Widget -->
<button onclick="toggleChat()" style="position: fixed; bottom: 50px; right: 20px; z-index: 1100;">Toggle Private Chat</button>
<div id="chatContainer" class="chat-widget" style="display: none;">
    <h2>Private Chat</h2>
    <div id="chatBox" class="chat-messages"></div>
    <textarea id="message" class="chat-input" placeholder="Type your message here..."></textarea>
    <button onclick="sendPrivateMessage()">Send</button>
</div>

<script>
function toggleChat() {
    var chatWidget = document.getElementById('chatContainer');
    chatWidget.style.display = (chatWidget.style.display === 'none' ? 'block' : 'none');
}

function sendPublicMessage() {
    var message = $('#publicMessage').val();
    if (message.trim() === '') {
        alert('Please enter a message');
        return; // Prevent sending an empty message
    }

    $.ajax({
        url: '../backend/sendPublicMessages.php', // Adjust the path as necessary
        type: 'POST',
        data: {message: message},
        success: function(response) {
            console.log("Response received:", response);
            $('#publicMessage').val(''); // Clear the input field
            fetchPublicMessages(); // Refresh the message list if necessary
        },
        error: function(xhr, status, error) {
            console.error('Error sending public message:', error);
        }
    });
}

function sendPrivateMessage() {
    var message = $('#message').val();
    if (message.trim() === '') {
        alert('Please enter a message');
        return; // Prevent sending an empty message
    }

    $.ajax({
        url: '../backend/sendMessage.php', // Adjust the path as necessary
        type: 'POST',
        data: {message: message},
        success: function(response) {
            console.log("Response received:", response);
            $('#message').val(''); // Clear the input field
            fetchPrivateMessages(); // Refresh the message list if necessary
        },
        error: function(xhr, status, error) {
            console.error('Error sending private message:', error);
        }
    });
}
function fetchPublicMessages() {
    $.ajax({
        url: '../backend/getPublicMessages.php', // Ensure the URL is correct
        type: 'GET',
        dataType: 'json',
        success: function(messages) {
            var chatBox = $('#publicChatBox');
            chatBox.html(''); // Clear the chat box before appending new messages
            messages.forEach(function(message) {
                chatBox.append('<p><strong>' + message.username + '</strong>: ' + message.message + '</p>');
            });
            chatBox.scrollTop(chatBox.prop("scrollHeight")); // Auto-scroll to the bottom
        },
        error: function(xhr, status, error) {
            console.error('Error fetching public messages:', error);
        }
    });
}
function fetchPrivateMessages() {
    $.ajax({
        url: '../backend/getMessages.php', // Ensure the URL is correct
        type: 'GET',
        dataType: 'json',
        success: function(messages) {
            var chatBox = $('#chatBox');
            chatBox.html(''); // Clear the chat box before appending new messages
            messages.forEach(function(message) {
                chatBox.append('<p><strong>' + message.username + '</strong>: ' + message.message + '</p>');
            });
            chatBox.scrollTop(chatBox.prop("scrollHeight")); // Auto-scroll to the bottom
        },
        error: function(xhr, status, error) {
            console.error('Error fetching private messages:', error);
        }
    });
}

setInterval(fetchPublicMessages, 2000);  // Polling public messages every 2 seconds
setInterval(fetchPrivateMessages, 2000);  // Polling private messages every 2 seconds

$(document).ready(function() {
    fetchPublicMessages(); // Initial fetch on page load
    fetchPrivateMessages(); // Initial fetch on page load
});
</script>
</body>
</html>

