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
    <title>Attendance History - HRMS</title>
    <style>
        body { background: #fff; font-family: Arial, sans-serif; margin: 0; color: #333; }
        h2 { color: #ff8800; }
        a { color: #ff8800; text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Attendance History</h2>
    <p>This is a placeholder for viewing attendance history.</p>
    <a href="employee_dashboard.php">Back to Dashboard</a>
</body>
</html> 