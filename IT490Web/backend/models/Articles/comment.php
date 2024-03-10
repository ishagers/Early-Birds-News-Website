<?php
require_once 'databaseFunctions.php'; //

$articleId = sanitizeInput($_POST['article_id']);
$userId = sanitizeInput($_POST['user_id']); // Assuming user authentication is handled elsewhere
$comment = sanitizeInput($_POST['comment']);

// Simple validation
if (!is_numeric($articleId) || !is_numeric($userId) || empty($comment)) {
    die("Invalid input.");
}

$stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $articleId, $userId, $comment);

if ($stmt->execute()) {
    echo "Comment added successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
