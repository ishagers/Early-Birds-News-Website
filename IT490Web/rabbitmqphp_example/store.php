<?php
require_once 'databaseFunctions.php';
// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p>You need to log in to access the store.</p>";
    exit;
}

function fetchStoreItems() {
    // Ideally, this function should fetch items from your database
    return [
        ['id' => 1, 'name' => 'Dark Mode', 'cost' => 100],
        ['id' => 2, 'name' => 'Custom Cursor', 'cost' => 150],
        ['id' => 3, 'name' => 'Alternative Theme', 'cost' => 200]
    ];
}

if (isset($_POST['purchase'])) {
    $itemId = $_POST['item_id'];
    $username = $_SESSION['username'];
    purchaseItem($username, $itemId);
}

function purchaseItem($username, $itemId) {
    $items = fetchStoreItems();

    foreach ($items as $item) {
        if ($item['id'] == $itemId) {
            $cost = $item['cost'];
            $currentEBP = fetchUserEBP($username);

            if ($currentEBP >= $cost) {
                $updatePointsResult = updateEBPoints($username, -$cost);

                if ($updatePointsResult['status']) {
                    $conn = getDatabaseConnection();

                    // Update the specific feature based on the item ID
                    $sql = "";
                    switch ($itemId) {
                        case 1: // Dark Mode
                            $sql = "UPDATE users SET has_dark_mode = 1 WHERE username = :username";
                            break;
                        case 2: // Custom Cursor
                            $sql = "UPDATE users SET has_custom_cursor = 1 WHERE username = :username";
                            break;
                        case 3: // Alternative Theme
                            $sql = "UPDATE users SET has_alternative_theme = 1 WHERE username = :username";
                            break;
                    }

                    if (!empty($sql)) {
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':username', $username);
                        $stmt->execute();
                        echo "<p>Purchase successful! You may now activate this feature by clicking its toggle button.</p>";

                        // Record the transaction
                        $transactionSql = "INSERT INTO transactions (user_id, item_id) VALUES ((SELECT id FROM users WHERE username = :username), :item_id)";
                        $transactionStmt = $conn->prepare($transactionSql);
                        $transactionStmt->bindParam(':username', $username);
                        $transactionStmt->bindParam(':item_id', $itemId);
                        $transactionStmt->execute();
                    }
                    return;

                } else {
                    echo "<p>" . $updatePointsResult['message'] . "</p>";
                    return;
                }
            } else {
                echo "<p>Insufficient EB Points.</p>";
                return;
            }
        }
    }
    echo "<p>Item not found.</p>";
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Store</title>
    <link rel="stylesheet" href="../routes/menuStyles.css">
</head>
<body>
    <?php require('nav.php'); ?>
    <div class="feature-buttons">
        <button onclick="toggleDarkMode()">Toggle Dark Mode</button>
        <button onclick="toggleCustomCursor()">Toggle Custom Cursor</button>
        <button onclick="toggleAlternativeTheme()">Toggle Alternative Theme</button>
    </div>

    <div class="store-items">
        <?php
        $items = fetchStoreItems();
        foreach ($items as $item) {
            echo '<div class="item">';
            echo '<h3>' . htmlspecialchars($item['name']) . '</h3>';
            echo '<p>Cost: ' . htmlspecialchars($item['cost']) . ' EB Points</p>';
            echo '<form action="" method="post">';
            echo '<input type="hidden" name="item_id" value="' . $item['id'] . '">';
            echo '<button type="submit" name="purchase">Buy Now</button>';
            echo '</form>';
            echo '</div>';
        }
        ?>
    </div>
    <script>
<script>
function toggleFeature(feature) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../backend/toggle_feature.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            alert(xhr.responseText);
        }
    };
    xhr.send('username=' + encodeURIComponent('<?php echo $_SESSION['username']; ?>') + '&feature=' + encodeURIComponent(feature));
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('toggleDarkMode').addEventListener('click', function() { toggleFeature('dark_mode'); });
    document.getElementById('toggleCustomCursor').addEventListener('click', function() { toggleFeature('custom_cursor'); });
    document.getElementById('toggleAlternativeTheme').addEventListener('click', function() { toggleFeature('alternative_theme'); });
});
</script>

</body>
</html>

