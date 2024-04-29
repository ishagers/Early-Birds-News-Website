<?php
require('session.php');
require_once 'databaseFunctions.php';

checkLogin();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['username'];

if (isset($_POST['purchase'])) {
    $itemId = $_POST['item_id'];
    $username = $_SESSION['username'];
    purchaseItem($username, $itemId);
}
if (isset($_POST['deactivateStyles'])) {
    $username = $_SESSION['username'];
    deactivateStyles($username);
}

$items = fetchStoreItems();
$userSettings = fetchUserSettings($username);
$themeStylePath = '../routes/menuStyles.css';
if ($userSettings['has_dark_mode']) {
    $themeStylePath = 'css/darkModeStyles.css'; // Dark mode style
} elseif ($userSettings['has_alternative_theme']) {
    $themeStylePath = 'css/alternativeThemeStyles.css'; // Alternative theme style
}
$cursorImagePath = "css/custom-cursor/sharingan-cursor.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Store</title>
    <link id="themeStyle" rel="stylesheet" href="<?php echo $themeStylePath; ?>" />
    <?php if ($userSettings['has_custom_cursor']): ?>
        <!-- Custom Cursor Style -->
    <link rel="stylesheet" href="css/custom-cursor/sharingan-cursor.png" />
    <?php endif; ?>
</head>
<body>
    <?php require('nav.php'); ?>

    <div class="store-items">
        <?php foreach ($items as $item): ?>
            <div class="item">
                <h3><?= htmlspecialchars($item['name']); ?></h3>
                <p>Cost: <?= htmlspecialchars($item['cost']); ?> EB Points</p>
                <form action="" method="post">
                    <input type="hidden" name="item_id" value="<?= $item['id']; ?>" />
                    <button type="submit" name="purchase">Buy Now</button>
                </form>
            </div>
        <?php endforeach; ?>
        <div class="item">
            <h3>Revert to Default Styles</h3>
            <form action="" method="post">
                <button type="submit" name="deactivateStyles">Revert Styles</button>
            </form>
        </div>
    </div>

</body>
</html>
