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
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            height: 400px;
            overflow: hidden; /* ensure internal scrolling behaves */
            background-color: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            padding: 10px;
            display: flex; /* Initially hidden */
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 10px;
        }

        .chat-input {
            width: calc(100% - 20px); /* accounting for padding */
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
        <span id="ebpPoints" class="eb-points"><?php echo $ebpPoints; ?></span>
    </div>
</div>

<!-- Chat Toggle Button -->
<button onclick="toggleChat()" style="position: fixed; bottom: 10px; right: 10px; z-index: 1100;">Toggle Chat</button>

<!-- Chat Container -->
<div id="chatContainer" class="chat-widget">
    <h2>Chat</h2>
    <div id="chatBox" class="chat-messages"></div>
    <textarea id="message" class="chat-input" placeholder="Type your message here..."></textarea>
    <button onclick="sendMessage()">Send</button>
</div>

<script>
function toggleChat() {
    var chatWidget = document.getElementById('chatContainer');
    chatWidget.style.display = (chatWidget.style.display === 'none' ? 'block' : 'none');
}

function fetchMessages() {
    console.log("Fetching messages...");
    $.ajax({
        url: '../backend/getMessages.php',
        type: 'GET',
        dataType: 'json',
        success: function(messages) {
            console.log("Messages fetched:", messages);
            var chatBox = $('#chatBox');
            chatBox.html(''); // Clear previous messages
            // Reverse the order of messages before appending them
            messages.reverse().forEach(function(message) {
                chatBox.append('<p><strong>' + message.username + '</strong>: ' + message.message + '</p>');
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching messages:', error);
            console.error('Detailed error:', xhr.responseText);
        }
    });
}

function sendMessage() {
    var username = '<?php echo $_SESSION['username']; ?>'; // Use username for identification
    var message = $('#message').val();
    console.log("Sending message:", message); // Debug: Output message to console
    $.post('../backend/sendMessage.php', { username: username, message: message }, function(response) {
        console.log("Response received:", response); // Debug: Output response to console
        $('#message').val(''); // Clear the message input box
        fetchMessages(); // Refresh messages to include the new one
    }).fail(function(xhr) {
        console.error('Error sending message:', xhr.responseText);
    });
}

setInterval(fetchMessages, 2000);  // Polling every 2 seconds
</script>

</body>
</html>
