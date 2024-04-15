document.addEventListener('DOMContentLoaded', function() {
    var conn = new WebSocket('ws://10.147.17.233:8080');
    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        var messages = document.getElementById('messages');
        messages.innerHTML += '<div>' + e.data + '</div>'; // Display new message
    };

    window.sendMessage = function() {
        var messageInput = document.getElementById('messageInput');
        conn.send(messageInput.value);
        messageInput.value = ''; // Clear input after sending
    };
});

