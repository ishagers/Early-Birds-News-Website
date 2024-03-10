<?php
require_once 'databaseFunctions.php';

$title = sanitizeInput($_POST['title']);
$content = sanitizeInput($_POST['content']);
$author = sanitizeInput($_POST['author']); // This could be a username or ID, depending on your setup

// Simple validation
if (empty($title) || empty($content) || empty($author)) {
    die("Invalid input.");
}

$stmt = $conn->prepare("INSERT INTO articles (title, content, author) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $title, $content, $author);

if ($stmt->execute()) {
    echo "Article authored successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
