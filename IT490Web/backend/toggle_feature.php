<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo "User not logged in.";
    exit;
}

// Check if the feature parameter is set
if (!isset($_POST['feature'])) {
    echo "Feature not specified.";
    exit;
}

$feature = $_POST['feature'];
$username = $_SESSION['username'];

// Toggle the feature
function toggleFeature($username, $feature) {
    $conn = getDatabaseConnection();
    $sql = "UPDATE users SET isActivated = JSON_SET(COALESCE(isActivated, '{}'), '$.$feature', NOT COALESCE(JSON_UNQUOTE(JSON_EXTRACT(isActivated, '$.$feature')), false)) WHERE username = :username";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);

    if ($stmt->execute()) {
        return "Feature toggled successfully.";
    } else {
        return "Failed to toggle feature.";
    }
}

echo toggleFeature($username, $feature);
?>

