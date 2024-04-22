<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat - Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        /* Existing styles... */
    </style>
</head>
<body>

<?php
require_once 'databaseFunctions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$ebpPoints = isset($_SESSION['username']) ? fetchUserEBP($_SESSION['username']) : 0;
include 'nav.php';
?>

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
    <button onclick="clearPublicChat()">Clear Chat</button>
</div>

<!-- Private Chat Widget with Friends List -->
<div id="chatContainer" class="chat-widget" style="display: none;">
    <h2>Private Chat</h2>
    <div class="friends-list">
        <ul id="friends"></ul>
    </div>
    <div class="chat-area">
        <div id="chatBox" class="chat-messages"></div>
        <textarea id="message" class="chat-input" placeholder="Type your message here..."></textarea>
        <button onclick="sendPrivateMessage()">Send</button>
    </div>
</div>

<button onclick="toggleChat()" style="position: fixed; bottom: 10px; right: 10px; z-index: 1100;">Toggle Chat</button>

<script>
var lastMessageId = 0; // Initialize with zero to fetch all messages initially
var activeFriend = null; // Store the active friend's ID

function toggleChat() {
    var chatWidget = document.getElementById('chatContainer');
    chatWidget.style.display = (chatWidget.style.display === 'none' ? 'block' : 'none');
}

function sendPublicMessage() {
    var message = $('#publicMessage').val();
    if (message.trim() === '') {
        alert('Please enter a message');
        return;
    }
    $.ajax({
        url: '../backend/sendPublicMessages.php',
        type: 'POST',
        data: { message: message },
        success: function(response) {
            console.log("Response received:", response);
            $('#publicMessage').val('');
            fetchPublicMessages();
        },
        error: function(xhr, status, error) {
            console.error('Error sending public message:', error);
        }
    });
}

function fetchPublicMessages() {
    $.ajax({
        url: '../backend/getPublicMessages.php',
        type: 'GET',
        data: { lastMessageId: lastMessageId },
        dataType: 'json',
        success: function(messages) {
            var chatBox = $('#publicChatBox');

            messages.forEach(function(message) {
                if (message.id > lastMessageId) {
                    chatBox.append(`<p><strong>${message.username}</strong>: ${message.message} <span>at ${message.timestamp}</span></p>`);
                    lastMessageId = message.id;
                }
            });

            chatBox.scrollTop(chatBox.prop("scrollHeight"));
        },
        error: function(xhr, status, error) {
            console.error('Error fetching public messages:', xhr.responseText);
        }
    });
}

function clearPublicChat() {
    $('#publicChatBox').empty();
    console.log("Chat cleared");
}

function fetchFriends() {
    $.ajax({
        url: '../backend/getFriendsList.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var friendsList = $('#friends');
            friendsList.empty();
            if (response && Array.isArray(response)) {
                response.forEach(function(friend) {
                    friendsList.append(`<li onclick="startChatWith('${friend.id}')">${friend.username}</li>`);
                });
            } else {
                console.error('Error fetching friends: No data received');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching friends:', error);
        }
    });
}

function startChatWith(friendId) {
    console.log("Starting chat with", friendId);
    $('#chatBox').html('');
    activeFriend = friendId;
    $('#chatContainer').find('.chat-header').text('Chatting with ' + friendId);
    fetchPrivateMessages(friendId);
    $('#chatContainer').show();
}

function sendPrivateMessage() {
    var message = $('#message').val();
    if (message.trim() === '') {
        alert('Please enter a message');
        return;
    }

    $.ajax({
        url: '../backend/sendMessage.php',
        type: 'POST',
        data: { message: message, receiver_id: activeFriend },
        success: function(response) {
            console.log("Response received:", response);
            $('#message').val('');
            fetchPrivateMessages(activeFriend);
        },
        error: function(xhr, status, error) {
            console.error('Error sending private message:', error);
        }
    });
}

function fetchPrivateMessages(recipientId) {
    $.ajax({
        url: '../backend/getMessages.php',
        type: 'GET',
        data: { recipient_id: recipientId },
        dataType: 'json',
        success: function(messages) {
            console.log(messages);
            var chatBox = $('#chatBox');
            chatBox.html('');

            if (Array.isArray(messages)) {
                messages.forEach(function(message) {
                    chatBox.append('<p><strong>' + message.username + '</strong>: ' + message.message + '</p>');
                });
            } else {
                console.error("Received data is not an array:", messages);
            }

            chatBox.scrollTop(chatBox.prop("scrollHeight"));
        },
        error: function(xhr, status, error) {
            console.error('Error fetching private messages:', error);
        }
    });
}

setInterval(fetchPublicMessages, 2000);

$(document).ready(function() {
    fetchPublicMessages();
    fetchFriends();
});
</script>
</body>
</html>
