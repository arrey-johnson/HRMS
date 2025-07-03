<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMPLOYEE') {
    header('Location: index.php');
    exit();
}
// Placeholder data
$employee_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Employee';
$leave_balance = 12;
$recent_attendance = [
    ['date' => '2024-07-08', 'status' => 'Present'],
    ['date' => '2024-07-07', 'status' => 'Remote'],
    ['date' => '2024-07-06', 'status' => 'Absent'],
];
$upcoming_events = [
    ['title' => 'Team Meeting', 'date' => '2024-07-10'],
    ['title' => 'Project Deadline', 'date' => '2024-07-15'],
];
$announcements = [
    ['title' => 'Office Closed', 'content' => 'The office will be closed on July 12th for maintenance.'],
    ['title' => 'New HR Policy', 'content' => 'Please review the updated HR policy in your email.'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Dashboard - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <style>
        body { background: #f6f8fb; font-family: 'Inter', Arial, sans-serif; margin: 0; color: #333; }
        .sidebar { width: 250px; background: #fff; color: #ff8800; height: 100vh; position: fixed; left: 0; top: 0; padding: 0; display: flex; flex-direction: column; z-index: 2; border-right: 1.5px solid #ffe0b3; }
        .sidebar .brand { display: flex; align-items: center; justify-content: center; height: 70px; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; background: #fff; color: #ff8800; border-bottom: 1px solid #ffe0b3; }
        .sidebar nav { flex: 1; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { }
        .sidebar a { display: flex; align-items: center; gap: 14px; color: #888; text-decoration: none; padding: 16px 32px; font-size: 1.08rem; font-weight: 500; transition: background 0.2s, color 0.2s; border-left: 4px solid transparent; }
        .sidebar a.active, .sidebar a:hover { background: #fff2e0; color: #ff8800; border-left: 4px solid #ff8800; }
        .sidebar a .icon { width: 20px; height: 20px; color: #ff8800; }
        .sidebar .logout { margin: 24px 32px 32px 32px; color: #fff; background: #ff8800; border-radius: 8px; padding: 10px 0; text-align: center; display: block; font-weight: 600; text-decoration: none; transition: background 0.2s, color 0.2s; }
        .sidebar .logout:hover { background: #fff; color: #ff8800; border: 1.5px solid #ff8800; }
        .main { margin-left: 250px; padding: 32px; background: #f6f8fb; min-height: 100vh; }
        h2 { color: #ff8800; margin-top: 0; }
        .dashboard-row { display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; flex: 1; min-width: 220px; margin-bottom: 24px; }
        .welcome-card { flex: 2; display: flex; align-items: center; gap: 18px; }
        .welcome-avatar { width: 60px; height: 60px; border-radius: 50%; border: 2px solid #ff8800; }
        .kpi-label { color: #888; font-size: 1rem; font-weight: 600; }
        .kpi-value { font-size: 2.1rem; color: #222; font-weight: 700; }
        .attendance-table, .events-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .attendance-table th, .attendance-table td, .events-table th, .events-table td { padding: 10px 8px; text-align: left; }
        .attendance-table th, .events-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .attendance-table td, .events-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .announcements-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px 32px; margin-bottom: 24px; }
        .announcement-title { color: #ff8800; font-weight: 700; margin-bottom: 6px; }
        .quick-actions { display: flex; gap: 18px; margin-top: 18px; }
        .quick-action-btn { background: #ff8800; color: #fff; border: none; border-radius: 16px; padding: 14px 28px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: background 0.2s; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .quick-action-btn:hover { background: #e67600; }
        @media (max-width: 900px) { .main { padding: 12px; } .dashboard-row { flex-direction: column; gap: 12px; } .card, .announcements-card { padding: 18px; } }
        .topnav { height: 64px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 1; margin-left: 250px; }
        .topnav .search-box { display: flex; align-items: center; background: #f6f8fb; border-radius: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 4px 12px; border: 1.5px solid #eee; margin: 0 auto; flex: 1; max-width: 400px; justify-content: center; }
        .topnav .search-box input[type="text"] { border: none; background: transparent; outline: none; font-size: 1rem; padding: 8px 8px; min-width: 180px; }
        .topnav .search-box button { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 8px 18px; font-size: 1rem; font-weight: 600; margin-left: 8px; cursor: pointer; transition: background 0.2s; }
        .topnav .search-box button:hover { background: #e67600; }
        .topnav .user-info { display: flex; align-items: center; gap: 10px; margin-left: 24px; }
        .topnav .welcome-avatar { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ff8800; }
        .topnav .user-name { font-size: 1.08rem; font-weight: 600; color: #222; }
        @media (max-width: 900px) { .topnav { margin-left: 0; flex-direction: column; gap: 10px; height: auto; padding: 12px; } .topnav .user-info { margin-left: 0; } }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="employee_dashboard.php" class="active"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="apply_leave.php"><span class="icon" data-feather="calendar"></span>Apply for Leave</a></li>
                <li><a href="payslips.php"><span class="icon" data-feather="file-text"></span>View Payslips</a></li>
                <li><a href="view_profile.php"><span class="icon" data-feather="user"></span>View Profile</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="topnav">
        <div class="search-box">
            <input type="text" placeholder="Search...">
            <button><span data-feather="search"></span></button>
        </div>
        <div class="user-info">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($employee_name); ?>&background=ff8800&color=fff" class="welcome-avatar" alt="avatar">
            <span class="user-name"><?php echo htmlspecialchars($employee_name); ?></span>
        </div>
    </div>
    <div class="main">
        <h2>Welcome, <?php echo htmlspecialchars($employee_name); ?>!</h2>
        <div class="dashboard-row">
            <div class="card welcome-card">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($employee_name); ?>&background=ff8800&color=fff" class="welcome-avatar" alt="avatar">
                <div>
                    <div style="font-size:1.3rem; font-weight:700; color:#222;">Hello, <?php echo htmlspecialchars($employee_name); ?>!</div>
                    <div style="color:#888; font-size:1.05rem;">Here's your dashboard overview.</div>
                </div>
            </div>
            <div class="card">
                <div class="kpi-label">Leave Balance</div>
                <div class="kpi-value"><?php echo $leave_balance; ?></div>
            </div>
            <div class="card">
                <div class="kpi-label">Recent Attendance</div>
                <table class="attendance-table">
                    <thead><tr><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($recent_attendance as $att): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($att['date']); ?></td>
                            <td><?php echo htmlspecialchars($att['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card">
                <div class="kpi-label">Upcoming Events</div>
                <table class="events-table">
                    <thead><tr><th>Event</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($upcoming_events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo htmlspecialchars($event['date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="announcements-card">
            <h3 style="color:#ff8800; margin-top:0;">Announcements</h3>
            <?php foreach ($announcements as $a): ?>
                <div class="announcement-title"><?php echo htmlspecialchars($a['title']); ?></div>
                <div style="margin-bottom:12px; color:#444;"> <?php echo htmlspecialchars($a['content']); ?></div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Quick Actions</h3>
            <div class="quick-actions">
                <a href="apply_leave.php" class="quick-action-btn"><span data-feather="calendar"></span>Apply for Leave</a>
                <a href="payslips.php" class="quick-action-btn"><span data-feather="file-text"></span>View Payslips</a>
                <a href="view_profile.php" class="quick-action-btn"><span data-feather="user"></span>View Profile</a>
            </div>
        </div>
    </div>
    <script>feather.replace();</script>
</body>
</html> 