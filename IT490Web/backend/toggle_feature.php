<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo "User not logged in.";
    exit;
}

if (!isset($_POST['feature'])) {
    echo "Feature not specified.";
    exit;
}

$feature = $_POST['feature'];
$username = $_SESSION['username'];

function toggleFeature($username, $feature) {
    $conn = getDatabaseConnection();

    // Map the feature to a JSON path dynamically and safely
    $featurePath = '$.' . preg_replace('/[^a-zA-Z0-9_]+/', '_', $feature);  // Sanitize feature name for JSON path

    // SQL to toggle the JSON attribute within isActivated
    $sql = "UPDATE users SET isActivated = JSON_SET(COALESCE(isActivated, '{}'), :featurePath, NOT COALESCE(JSON_UNQUOTE(JSON_EXTRACT(isActivated, :featurePath)), false)) WHERE username = :username";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindValue(':featurePath', $featurePath);

    if ($stmt->execute()) {
        return "Feature toggled successfully.";
    } else {
        return "Failed to toggle feature.";
    }
}

echo toggleFeature($username, $feature);
?>

