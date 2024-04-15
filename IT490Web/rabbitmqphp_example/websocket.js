document.addEventListener('DOMContentLoaded', function() {
    var conn = new WebSocket('ws://10.147.17.233:8080?token=' + token);


    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        var messages = document.getElementById('messages');
        var messageDiv = document.createElement('div'); // Create a new div for each message
        messageDiv.textContent = e.data; // Safe text content
        messages.appendChild(messageDiv); // Append new message div to the messages container
    };

    conn.onerror = function(error) {
        console.error('WebSocket Error:', error);
    };

    conn.onclose = function(e) {
        console.log('Connection closed', e);
    };

    window.sendMessage = function() {
        var messageInput = document.getElementById('messageInput');
        if (messageInput.value.trim() !== '') { // Check if the message is not just whitespace
            conn.send(messageInput.value);
            messageInput.value = ''; // Clear input after sending
        }
    };
});

