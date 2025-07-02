<?php
require 'db.php';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['id']) &&
    isset($_POST['name'], $_POST['marital_status'], $_POST['phone'], $_POST['address'], $_POST['email'])
) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $marital = trim($_POST['marital_status']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $stmt = $conn->prepare('UPDATE users SET name=?, marital_status=?, phone=?, address=?, email=? WHERE id=?');
    $stmt->bind_param('sssssi', $name, $marital, $phone, $address, $email, $id);
    $stmt->execute();
    $stmt->close();
    echo 'success';
} else {
    echo 'error';
}
?> 