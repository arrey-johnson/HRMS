<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Monitor Performance - HRMS</title>
    <style>
        body { background: #fff; font-family: Arial, sans-serif; margin: 0; color: #333; }
        h2 { color: #ff8800; }
        a { color: #ff8800; text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Monitor Performance & Appraisals</h2>
    <p>This is a placeholder for monitoring performance and appraisals.</p>
    <a href="hr_dashboard.php">Back to Dashboard</a>
</body>
</html> 