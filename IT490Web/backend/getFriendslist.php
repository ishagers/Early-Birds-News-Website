<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';
$db = getDatabaseConnection();
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $db->prepare("
    SELECT u.id, u.username 
    FROM users u
    JOIN friends f ON f.user_id2 = u.id OR f.user_id1 = u.id
    WHERE (f.user_id1 = :user_id OR f.user_id2 = :user_id) AND f.status = 'accepted' AND u.id != :user_id
");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($friends);

$db = null;
?>

