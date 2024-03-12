<?php
// This script would check the response queue for a message with the correct correlation ID

$correlationId = $_GET['correlationId'] ?? '';

// Simulate checking the response queue
// In a real scenario, you'd connect to RabbitMQ and check for a message matching the correlationId
if ($correlationId === 'someExpectedCorrelationId') {
    echo json_encode(['status' => 'success', 'message' => 'User authenticated']);
} else {
    echo json_encode(['status' => 'waiting']);
}
?>
