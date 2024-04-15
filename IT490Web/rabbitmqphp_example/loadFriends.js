function loadFriends() {
    fetch('fetchFriends.php')
    .then(response => response.json())
    .then(data => {
        data.forEach(friend => {
            // Append each friend to a list in your chat widget
        });
    });
}

