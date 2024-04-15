document.addEventListener('DOMContentLoaded', function() {
    var conn = new WebSocket('ws://10.147.17.233:8080?token=' + token);


    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        var data = JSON.parse(e.data);
        if (data.fromUserId === selectedFriendId || data.fromUserId === "server") {  // Ensure messages are from the selected friend or server
            var messages = document.getElementById('messages');
            var messageDiv = document.createElement('div');
            messageDiv.textContent = data.message;  // Display the message
            messages.appendChild(messageDiv);
        }
    };

    window.selectFriend = function(friendId, friendName) {
        selectedFriendId = friendId;
        document.getElementById('chat-title').textContent = 'Chatting with ' + friendName;
        document.getElementById('messages').innerHTML = '';  // Optionally clear messages when changing friend
    };

    window.sendMessage = function() {
        var messageInput = document.getElementById('messageInput');
        if (messageInput.value.trim() !== '' && selectedFriendId) {
            var message = {
                type: 'private',
                targetUserId: selectedFriendId,
                message: messageInput.value
            };
            conn.send(JSON.stringify(message));
            messageInput.value = '';  // Clear input after sending
        }
    };
});
