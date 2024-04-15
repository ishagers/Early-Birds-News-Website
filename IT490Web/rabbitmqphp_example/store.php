<?php
require_once 'databaseFunctions.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p>You need to log in to access the store.</p>";
    exit;
}

// Fetch items from the database
function fetchStoreItems() {
    // Implement this function to fetch items from your database
    return [
        ['id' => 1, 'name' => 'Dark Mode', 'cost' => 100],
        ['id' => 2, 'name' => 'Custom Cursor', 'cost' => 150],
        ['id' => 3, 'name' => 'Alternative Theme', 'cost' => 200]
    ];
}

// Handle item purchase
if (isset($_POST['purchase'])) {
    $itemId = $_POST['item_id'];
    $username = $_SESSION['username'];
    // Implement this function to process the purchase
    purchaseItem($username, $itemId);
}

// Example function to process a purchase
function purchaseItem($username, $itemId) {
    $items = fetchStoreItems();
    foreach ($items as $item) {
        if ($item['id'] == $itemId) {
            $cost = $item['cost'];
            // Subtract cost from user's EB points and update database
            $currentEBP = fetchUserEBP($username);
            if ($currentEBP >= $cost) {
                updateEBPoints($username, -$cost);
                echo "<p>Purchase successful!</p>";
                return;
            } else {
                echo "<p>Insufficient EB Points.</p>";
                return;
            }
        }
    }
    echo "<p>Item not found.</p>";
}

?>

