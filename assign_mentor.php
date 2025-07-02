<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'], $_POST['mentor_id'])) {
    $admin_id = intval($_POST['admin_id']);
    $mentor_id = intval($_POST['mentor_id']);
    // Optionally: check if already assigned and update instead of insert
    $stmt = $conn->prepare('REPLACE INTO intern_mentors (admin_id, mentor_id, assigned_at) VALUES (?, ?, NOW())');
    $stmt->bind_param('ii', $admin_id, $mentor_id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    $stmt->close();
} else {
    echo 'error';
} 