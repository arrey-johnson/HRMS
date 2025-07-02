<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMPLOYEE') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Apply for Leave - HRMS</title>
    <style>
        body { background: #fff; font-family: Arial, sans-serif; margin: 0; color: #333; }
        h2 { color: #ff8800; }
        a { color: #ff8800; text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Apply for Leave</h2>
    <p>This is a placeholder for applying for leave and viewing leave status.</p>
    <a href="employee_dashboard.php">Back to Dashboard</a>
</body>
</html> 