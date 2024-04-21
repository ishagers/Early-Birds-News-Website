<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat - Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
	  .friends-panel {
	    width: 200px;
	    position: fixed;
	    left: 0;
	    top: 0;
	    height: 100%;
	    overflow-y: auto;
	    background-color: #f9f9f9;
	    border-right: 1px solid #ccc;
	}

	.friends-panel h3 {
	    padding: 10px;
	    background-color: #eee;
	    margin: 0;
	}

	.friends-panel ul {
	    list-style: none;
	    padding: 0;
	    margin: 0;
	}

	.friends-panel ul li {
	    padding: 10px;
	    cursor: pointer;
	}

	.friends-panel ul li:hover {
	    background-color: #ddd;
	}

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
            background-color: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
            padding: 10px;
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
    <button onclick="clearPublicChat()">Clear Chat</button> <!-- New button to clear chat -->
</div>

<!-- Private Chat Widget with Friends List -->
<div id="chatContainer" class="chat-widget" style="display: none;">
    <h2>Private Chat</h2>
    <div class="friends-list">
        <input type="text" placeholder="Search friends..." oninput="filterFriends(this.value)">
        <ul id="friends"></ul>
    </div>
    <div id="chatBox" class="chat-messages"></div>
    <textarea id="message" class="chat-input" placeholder="Type your message here..."></textarea>
    <button onclick="sendPrivateMessage()">Send</button>
</div>

<button onclick="toggleChat()" style="position: fixed; bottom: 10px; right: 10px; z-index: 1100;">Toggle Chat</button>

<script>

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
        dataType: 'json',
        success: function(messages) {
            var chatBox = $('#publicChatBox');
            chatBox.html('');
            messages.forEach(function(message) {
                // Adjust the display formatting if necessary
                chatBox.append(`<p><strong>${message.username}</strong>: ${message.message} <span>at ${message.timestamp}</span></p>`);
            });
            chatBox.scrollTop(chatBox.prop("scrollHeight"));
        },
        error: function(xhr, status, error) {
            console.error('Error fetching public messages:', xhr.responseText);
        }
    });
}


function clearPublicChat() {
    stopFetchingMessages(); // Stop fetching when clearing chat
    $('#publicChatBox').empty(); // Clears the chat box
    isFetchingActive = false; // Optionally stop fetching
}

function fetchFriends() {
    $.ajax({
        url: '../backend/getFriendsList.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === "success" && Array.isArray(response.data)) {
                var friendsList = $('#friends');
                friendsList.empty(); // Clear existing entries
                response.data.forEach(function(friend) {
                    friendsList.append(`<li onclick="startChatWith('${friend.id}')">${friend.username}</li>`);
                });
            } else {
                console.error('Received data is not an array:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching friends:', error);
        }
    });
}


function startChatWith(friendId) {
    console.log("Starting chat with", friendId);
    // Further code to open or focus the chat window with this friend
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
        data: { message: message },
        success: function(response) {
            console.log("Response received:", response);
            $('#message').val('');
            fetchPrivateMessages();
        },
        error: function(xhr, status, error) {
            console.error('Error sending private message:', error);
        }
    });
}
function fetchPrivateMessages() {
    $.ajax({
        url: '../backend/getMessages.php',
        type: 'GET',
        dataType: 'json',
        success: function(messages) {
            var chatBox = $('#chatBox');
            chatBox.html('');
            messages.forEach(function(message) {
                chatBox.append('<p><strong>' + message.username + '</strong>: ' + message.message + '</p>');
            });
            chatBox.scrollTop(chatBox.prop("scrollHeight"));
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

