var selectedFriendId = ''; // Global variable to store the current friend's user ID

document.addEventListener('DOMContentLoaded', function() {
    var conn = new WebSocket('ws://10.147.17.233:8080?token=' + token);

    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        var data = JSON.parse(e.data);
        if (data.fromUserId === selectedFriendId || data.from === 'server') {
            displayMessage(data.message, data.fromUserId);
        }
    };

    conn.onerror = function(error) {
        console.error('WebSocket Error:', error);
    };

    conn.onclose = function(e) {
        console.log('Connection closed', e);
    };

    window.startChatWith = function(friendId, friendName) {
        selectedFriendId = friendId; 
        document.getElementById('chat-title').textContent = 'Chatting with ' + friendName;
        document.getElementById('messages').innerHTML = ''; // Clear previous messages
        console.log("Chat started with: " + friendName); 
    };

    window.sendMessage = function() {
        var messageInput = document.getElementById('messageInput');
        if (messageInput.value.trim() !== '' && selectedFriendId) {
            var message = {
                type: 'message',
                targetUserId: selectedFriendId,
                message: messageInput.value
            };
            conn.send(JSON.stringify(message)); // Send the message as a stringified JSON
            messageInput.value = ''; // Clear input after sending
        }
    };
});

function displayMessage(message, fromUserId) {
    var messages = document.getElementById('messages');
    console.log('Attempting to display message:', message, 'from:', fromUserId);
    if (messages) {
        var messageDiv = document.createElement('div');
        messageDiv.textContent = `${fromUserId}: ${message}`; // Display format
        messages.appendChild(messageDiv);
        console.log('Message appended to container');
    } else {
        console.error('Messages container not found!');
    }
}

