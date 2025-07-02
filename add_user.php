<?php
require 'db.php';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset(
        $_POST['first_name'], $_POST['last_name'], $_POST['username'], $_POST['phone'],
        $_POST['role'], $_POST['marital_status'], $_POST['email'], $_POST['password'], $_POST['confirm'], $_POST['address']
    )
) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $marital_status = $_POST['marital_status'];
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    if ($password !== $confirm) {
        echo 'Passwords do not match!';
        exit;
    }
    if (empty($first_name) || empty($last_name) || empty($username) || empty($phone) || empty($role) || empty($marital_status) || empty($email) || empty($password) || empty($address)) {
        echo 'All fields are required!';
        exit;
    }
    $name = $first_name . ' ' . $last_name;
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, username, phone, role, marital_status, address, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssssss', $name, $username, $phone, $role, $marital_status, $address, $email, $hashed);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error: ' . $stmt->error;
    }
    $stmt->close();
} else {
    echo 'error';
}
?> 