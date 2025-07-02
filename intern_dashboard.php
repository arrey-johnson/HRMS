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
    <title>Intern Dashboard - HRMS</title>
    <style>
        body { background: #fff; font-family: Arial, sans-serif; margin: 0; color: #333; }
        .sidebar { width: 220px; background: #ff8800; color: #fff; height: 100vh; position: fixed; left: 0; top: 0; padding: 32px 0 0 0; }
        .sidebar h2 { color: #fff; text-align: center; margin-bottom: 32px; }
        .sidebar a { display: block; color: #fff; text-decoration: none; padding: 14px 32px; font-size: 1.1rem; transition: background 0.2s; }
        .sidebar a:hover { background: #fff; color: #ff8800; }
        .main { margin-left: 220px; padding: 32px; }
        .logout { position: absolute; bottom: 32px; left: 32px; color: #fff; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Intern</h2>
        <a href="intern_dashboard.php">Dashboard</a>
        <a href="intern_profile.php">View/update profile</a>
        <a href="project_details.php">View assigned project</a>
        <a href="supervisor_communication.php">Communicate with supervisor</a>
        <a href="submit_reports.php">Submit reports</a>
        <a href="training_resources.php">View training resources</a>
        <a class="logout" href="logout.php">Logout</a>
    </div>
    <div class="main">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p>Select an option from the menu to get started.</p>
    </div>
</body>
</html> 