<?php
require_once 'databaseFunctions.php';
require_once 'nav.php'; 


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
                    if ($itemId == 1) {  // Assuming ID 1 is Dark Mode
                        $conn = getDatabaseConnection();
                        $sql = "UPDATE users SET has_dark_mode = true WHERE username = :username";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':username', $username);
                        $stmt->execute();
                    }
                    echo "<p>Purchase successful!</p>";
                } else {
                    echo "<p>" . $updatePointsResult['message'] . "</p>";
                }
                return;
            } else {
                echo "<p>Insufficient EB Points.</p>";
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
    <link rel="stylesheet" href="css/futuristicStyles.css">
</head>
<body>
    <div class="header">
        <h1>Store</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
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
</body>
</html>

