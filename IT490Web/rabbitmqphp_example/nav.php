<?php

require_once 'databaseFunctions.php';

// Check if the session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the username is set in the session to customize the greeting
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$ebpPoints = isset($_SESSION['username']) ? fetchUserEBP($_SESSION['username']) : 0;

if (isset($_POST['add_ebp'])) {
    if (isset($_SESSION['username'])) {
        $currencyResponse = addCurrencyToUserByUsername($_SESSION['username'], 5);
        echo "<p>" . htmlspecialchars($currencyResponse['message']) . "</p>";
    } else {
        echo "<p>User must be logged in to receive currency.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Early Bird Articles</title>
    <link rel="stylesheet" href="../routes/menuStyles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<div class="header">
    <h1>Early Bird Articles</h1>
    <div class="user-info">
        Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
        <form method="post" action="">
            <button type="submit" name="add_ebp">Add 5 EBP</button>
        </form>
        <img src="../assets/EBP.png" alt="EB Points:" width="32" height="32" />
        <span class="eb-points-label">EB Points:</span>
        <span id="ebpPoints" class="eb-points"><?php echo $ebpPoints; ?></span>
    </div>
</div>

<div class="nav-bar">
    <ul>
        <li><a href="writeArticle.php">Create Article</a></li>
        <li><a href="article-history.php">Article History</a></li>
        <li><a href="accountPreferences.php">Profile Preferences</a></li>
        <li><a href="privateArticle.php">Private</a></li>
        <li><a href="SearchArticles.php">Search Articles</a></li>
        <li><a href="mainMenu.php">Home</a></li>
        <li><a href="NewsAPIData.php">Latest News</a></li>
        <li><a href="store.php">Store</a></li>
    </ul>
</div>

<!-- Chat Container -->
<div id="chatContainer" style="margin: 20px;">
    <h2>Chat</h2>
    <div id="chatBox" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 5px;"></div>
    <textarea id="message" placeholder="Type your message here..." style="width: 100%;"></textarea>
    <button onclick="sendMessage()">Send</button>
</div>

<script>
function fetchMessages() {
    $.ajax({
        url: 'getMessages.php',
        type: 'GET',
        success: function(data) {
            var messages = JSON.parse(data);
            $('#chatBox').html('');
            $.each(messages, function(i, message) {
                $('#chatBox').append('<p><strong>' + message.username + '</strong>: ' + message.message + '</p>');
            });
        }
    });
}

function sendMessage() {
    var userId = '<?php echo $_SESSION['user_id']; ?>';  // Inject the user's ID into the script
    var message = $('#message').val();
    $.post('sendMessage.php', { user_id: userId, message: message }, function(response) {
        $('#message').val(''); // Clear the message input box
        fetchMessages(); // Fetch messages to update the chat
    });
}

setInterval(fetchMessages, 2000);  // Polling every 2 seconds

// Update EBP points periodically
setInterval(function() {
    fetch('getEBP.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('ebpPoints').textContent = data;
        });
}, 5000); // Update every 5 seconds
</script>

</body>
</html>

