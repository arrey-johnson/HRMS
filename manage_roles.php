<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'db.php';
$is_hr_manager = ($_SESSION['user_role'] === 'HR MANAGER');
$is_admin = ($_SESSION['user_role'] === 'ADMIN');
$is_manager = ($_SESSION['user_role'] === 'MANAGER');

// Fetch permissions (replace with your actual table/columns)
$permissions = [];
$sql = "SELECT rp.id, u.username, u.role, rp.permission_type, rp.start_date, rp.end_date, rp.status FROM role_permissions rp JOIN users u ON rp.user_id = u.id ORDER BY rp.id DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Roles & Permissions - HRMS</title>
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
        .permissions-table-container { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 1100px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 12px 10px; text-align: left; }
        th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        tr { background: #fff; transition: background 0.2s; }
        tr:hover { background: #fff7ec; }
        td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .status-badge { display: inline-block; border-radius: 12px; padding: 4px 14px; font-size: 0.95rem; font-weight: 600; }
        .status-approved { background: #e6f9ed; color: #15c26b; }
        .status-pending { background: #fffbe6; color: #ff8800; }
        .status-refused { background: #ffeaea; color: #ff3333; }
        @media (max-width: 1100px) { .main { padding: 16px; } .permissions-table-container { padding: 12px; } }
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
                <li><a href="manage_roles.php" class="active"><span class="icon" data-feather="shield"></span>Manage roles & permissions</a></li>
                <li><a href="analytics_logs.php"><span class="icon" data-feather="bar-chart-2"></span>View analytics & logs</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="topnav">
        <div class="search-area">
            <form class="search-box" method="get" action="#">
                <input type="text" name="search" placeholder="Search...">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="profile" style="margin-left:auto; display:flex; align-items:center; gap:18px;">
            <span style="font-weight:600; color:#333;">Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=ff8800&color=fff" alt="avatar" class="avatar">
        </div>
    </div>
    <div class="main">
        <h2>Roles & Permissions</h2>
        <div class="permissions-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Permission Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($permissions) === 0): ?>
                    <tr><td colspan="7" style="text-align:center; color:#aaa;">No permissions found.</td></tr>
                <?php else: ?>
                    <?php foreach ($permissions as $perm): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($perm['id']); ?></td>
                        <td><?php echo htmlspecialchars($perm['username']); ?></td>
                        <td><?php echo htmlspecialchars($perm['role']); ?></td>
                        <td><?php echo htmlspecialchars($perm['permission_type']); ?></td>
                        <td><?php echo htmlspecialchars($perm['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($perm['end_date']); ?></td>
                        <td>
                            <?php
                                $status = strtolower($perm['status']);
                                $badge = 'status-badge ';
                                if ($status === 'approved') $badge .= 'status-approved';
                                elseif ($status === 'pending') $badge .= 'status-pending';
                                else $badge .= 'status-refused';
                            ?>
                            <span class="<?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:18px; color:#888; font-size:0.98rem;">
                <?php if ($is_hr_manager): ?>
                    You can approve or refuse permissions from the HR dashboard.
                <?php elseif ($is_manager): ?>
                    You can only view permissions. Approval is handled by the HR manager.
                <?php else: ?>
                    Only HR managers can approve or refuse permissions.
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>feather.replace();</script>
</body>
</html> 