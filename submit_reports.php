<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'INTERN') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Project Reports - HRMS</title>
    <style>
        body { background: #fff; font-family: Arial, sans-serif; margin: 0; color: #333; }
        h2 { color: #ff8800; }
        a { color: #ff8800; text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Submit Project Reports</h2>
    <p>This is a placeholder for submitting project reports or weekly updates.</p>
    <a href="intern_dashboard.php">Back to Dashboard</a>
</body>
</html> 