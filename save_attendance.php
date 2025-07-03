<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['status'])) {
    $user_id = intval($_POST['user_id']);
    $status = $_POST['status'];
    $today = date('Y-m-d');
    // Insert or update today's attendance
    $stmt = $conn->prepare('INSERT INTO attendance (user_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
    $stmt->bind_param('iss', $user_id, $today, $status);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    $stmt->close();
} else {
    echo 'error';
} 