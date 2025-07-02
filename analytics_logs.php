<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'db.php';

// Fetch recent logins (limit 6)
$recent_logins = [];
$sql = "SELECT l.id, u.username, u.role, l.login_time FROM logins l JOIN users u ON l.user_id = u.id ORDER BY l.login_time DESC LIMIT 6";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_logins[] = $row;
    }
}
// Fetch all logins (for dropdown)
$all_logins = [];
$sql_all = "SELECT l.id, u.username, u.role, l.login_time FROM logins l JOIN users u ON l.user_id = u.id ORDER BY l.login_time DESC LIMIT 6, 1000";
$result_all = $conn->query($sql_all);
if ($result_all) {
    while ($row = $result_all->fetch_assoc()) {
        $all_logins[] = $row;
    }
}
// Search logic
$user_info = null;
$user_attendance = [];
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = $conn->real_escape_string($_GET['search']);
    $sql_user = "SELECT * FROM users WHERE username='$search' OR id='$search' LIMIT 1";
    $res_user = $conn->query($sql_user);
    if ($res_user && $res_user->num_rows > 0) {
        $user_info = $res_user->fetch_assoc();
        // Fetch attendance (placeholder: use your actual attendance table/logic)
        $sql_att = "SELECT * FROM attendance WHERE user_id=" . intval($user_info['id']) . " ORDER BY date DESC";
        $res_att = $conn->query($sql_att);
        if ($res_att) {
            while ($row = $res_att->fetch_assoc()) {
                $user_attendance[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Analytics & Logs - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <style>
        body { background: #f6f8fb; font-family: 'Inter', Arial, sans-serif; margin: 0; color: #333; }
        .sidebar { width: 250px; background: #ff8800; color: #fff; height: 100vh; position: fixed; left: 0; top: 0; padding: 0; display: flex; flex-direction: column; z-index: 2; }
        .sidebar .brand { display: flex; align-items: center; justify-content: center; height: 70px; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; background: #fff; color: #ff8800; border-bottom: 1px solid #ffe0b3; }
        .sidebar nav { flex: 1; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { }
        .sidebar a { display: flex; align-items: center; gap: 14px; color: #fff; text-decoration: none; padding: 16px 32px; font-size: 1.08rem; font-weight: 500; transition: background 0.2s, color 0.2s; border-left: 4px solid transparent; }
        .sidebar a.active, .sidebar a:hover { background: #fff; color: #ff8800; border-left: 4px solid #ff8800; }
        .sidebar a .icon { width: 20px; height: 20px; }
        .sidebar .logout { margin: 24px 32px 32px 32px; color: #fff; background: #fff2e0; border-radius: 8px; padding: 10px 0; text-align: center; display: block; font-weight: 600; text-decoration: none; transition: background 0.2s, color 0.2s; }
        .sidebar .logout:hover { background: #fff; color: #ff8800; }
        .topnav { height: 64px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 1; }
        .topnav .search-area { flex: 1; display: flex; justify-content: center; }
        .search-box { display: flex; align-items: center; background: #f6f8fb; border-radius: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 4px 12px; }
        .search-box input[type="text"] { border: none; background: transparent; outline: none; font-size: 1rem; padding: 8px 8px; min-width: 220px; }
        .search-box button { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 8px 18px; font-size: 1rem; font-weight: 600; margin-left: 8px; cursor: pointer; transition: background 0.2s; }
        .search-box button:hover { background: #e67600; }
        .main { margin-left: 250px; padding: 32px; background: #f6f8fb; min-height: 100vh; }
        h2 { color: #ff8800; margin-top: 0; }
        .logs-container { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 900px; margin: 0 auto 32px auto; }
        .logs-list { margin-bottom: 18px; }
        .logs-list table { width: 100%; border-collapse: collapse; }
        .logs-list th, .logs-list td { padding: 10px 8px; text-align: left; }
        .logs-list th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .logs-list tr { background: #fff; transition: background 0.2s; }
        .logs-list tr:hover { background: #fff7ec; }
        .logs-list td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .dropdown-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 6px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-bottom: 10px; }
        .dropdown-btn:hover { background: #e67600; }
        .all-logs { display: none; margin-top: 10px; }
        .search-user-container { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 900px; margin: 0 auto 32px auto; }
        .user-info { margin-top: 18px; }
        .user-info-table, .attendance-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .user-info-table th, .user-info-table td, .attendance-table th, .attendance-table td { padding: 10px 8px; text-align: left; }
        .user-info-table th, .attendance-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .user-info-table td, .attendance-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        @media (max-width: 1100px) { .main { padding: 16px; } .logs-container, .search-user-container { padding: 12px; } }
        @media (max-width: 700px) { .sidebar, .topnav { display: none; } .main { margin-left: 0; padding: 8px; } }
        .topnav .avatar { width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ff8800; }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="manage_users.php"><span class="icon" data-feather="users"></span>Manage all users</a></li>
                <li><a href="global_settings.php"><span class="icon" data-feather="settings"></span>Set global settings</a></li>
                <li><a href="manage_roles.php"><span class="icon" data-feather="shield"></span>Manage roles & permissions</a></li>
                <li><a href="analytics_logs.php" class="active"><span class="icon" data-feather="bar-chart-2"></span>View analytics & logs</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="topnav">
        <div class="search-area">
            <form class="search-box" method="get" action="#">
                <input type="text" name="search" placeholder="Search by username or ID..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="profile" style="margin-left:auto; display:flex; align-items:center; gap:18px;">
            <span style="font-weight:600; color:#333;">Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=ff8800&color=fff" alt="avatar" class="avatar">
        </div>
    </div>
    <div class="main">
        <h2>Analytics & Logs</h2>
        <div class="logs-container">
            <div class="logs-list">
                <h3 style="margin-top:0; color:#ff8800;">Recent Logins</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Login Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logins as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['id']); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['role']); ?></td>
                            <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($all_logins) > 0): ?>
            <button class="dropdown-btn" onclick="document.getElementById('allLogs').style.display = (document.getElementById('allLogs').style.display === 'block' ? 'none' : 'block');">Show All Logins</button>
            <div class="all-logs" id="allLogs">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Login Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_logins as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['id']); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['role']); ?></td>
                            <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <div class="search-user-container">
            <h3 style="margin-top:0; color:#ff8800;">Search User Info & Attendance</h3>
            <form class="search-box" method="get" action="">
                <input type="text" name="search" placeholder="Enter username or ID..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </form>
            <?php if ($user_info): ?>
            <div class="user-info">
                <h4 style="color:#ff8800;">Personal Information</h4>
                <table class="user-info-table">
                    <tr><th>ID</th><td><?php echo htmlspecialchars($user_info['id']); ?></td></tr>
                    <tr><th>Username</th><td><?php echo htmlspecialchars($user_info['username']); ?></td></tr>
                    <tr><th>Full Name</th><td>
                        <?php
                            $fname = isset($user_info['first_name']) ? $user_info['first_name'] : '';
                            $lname = isset($user_info['last_name']) ? $user_info['last_name'] : '';
                            echo htmlspecialchars(trim($fname . ' ' . $lname));
                        ?>
                    </td></tr>
                    <tr><th>Email</th><td><?php echo htmlspecialchars($user_info['email']); ?></td></tr>
                    <tr><th>Role</th><td><?php echo htmlspecialchars($user_info['role']); ?></td></tr>
                    <tr><th>Phone</th><td><?php echo htmlspecialchars($user_info['phone']); ?></td></tr>
                    <tr><th>Marital Status</th><td><?php echo htmlspecialchars($user_info['marital_status']); ?></td></tr>
                    <tr><th>Address</th><td><?php echo htmlspecialchars($user_info['address']); ?></td></tr>
                    <tr><th>Created At</th><td><?php echo htmlspecialchars($user_info['created_at']); ?></td></tr>
                </table>
                <h4 style="color:#ff8800;">Attendance</h4>
                <table class="attendance-table">
                    <thead>
                        <tr><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($user_attendance) === 0): ?>
                        <tr><td colspan="2" style="text-align:center; color:#aaa;">No attendance records found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($user_attendance as $att): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($att['date']); ?></td>
                            <td><?php echo htmlspecialchars($att['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif (isset($_GET['search'])): ?>
                <div style="color:#ff3333; margin-top:12px;">No user found with that username or ID.</div>
            <?php endif; ?>
        </div>
    </div>
    <script>feather.replace();</script>
</body>
</html> 