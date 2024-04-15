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
    $.ajax({
        url: '../backend/getMessages.php',
        type: 'GET',
        dataType: 'json',  // Ensures jQuery treats the response as JSON.
        success: function(messages) {
            var chatBox = $('#chatBox');
            chatBox.html(''); // Clear previous messages
            $.each(messages, function(i, message) {
                chatBox.append('<p><strong>' + message.username + '</strong>: ' + message.message + '</p>');
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching messages:', error);
        }
    });
}

function sendMessage() {
    var userId = '<?php echo $_SESSION['user_id']; ?>';  // Inject the user's ID into the script
    var message = $('#message').val();
    $.post('../backend/sendMessage.php', { user_id: userId, message: message }, function(response) {
        $('#message').val(''); // Clear the message input box
        fetchMessages(); // Fetch messages to update the chat
    }).fail(function(error) {
        console.error('Error sending message:', error);
    });
}

setInterval(fetchMessages, 2000);  // Polling every 2 seconds
</script>

</body>
</html>
