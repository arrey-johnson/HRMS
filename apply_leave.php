<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMPLOYEE') {
    header('Location: index.php');
    exit();
}
$employee_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Employee';
// Placeholder leave requests
$leave_requests = [
    [
        'type' => 'Sick Leave',
        'from' => '2024-07-10',
        'to' => '2024-07-12',
        'reason' => 'Fever',
        'status' => 'Pending'
    ],
    [
        'type' => 'Annual Leave',
        'from' => '2024-07-15',
        'to' => '2024-07-20',
        'reason' => 'Vacation',
        'status' => 'Approved'
    ]
];
$success = $error = '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Apply for Leave - HRMS</title>
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
        .topnav { height: 64px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 1; margin-left: 250px; }
        .topnav .search-box { display: flex; align-items: center; background: #f6f8fb; border-radius: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 4px 12px; border: 1.5px solid #eee; margin: 0 auto; flex: 1; max-width: 400px; justify-content: center; }
        .topnav .search-box input[type="text"] { border: none; background: transparent; outline: none; font-size: 1rem; padding: 8px 8px; min-width: 180px; }
        .topnav .search-box button { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 8px 18px; font-size: 1rem; font-weight: 600; margin-left: 8px; cursor: pointer; transition: background 0.2s; }
        .topnav .search-box button:hover { background: #e67600; }
        .topnav .user-info { display: flex; align-items: center; gap: 10px; margin-left: 24px; }
        .topnav .welcome-avatar { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ff8800; }
        .topnav .user-name { font-size: 1.08rem; font-weight: 600; color: #222; }
        @media (max-width: 900px) { .topnav { margin-left: 0; flex-direction: column; gap: 10px; height: auto; padding: 12px; } .topnav .user-info { margin-left: 0; } }
        .main { margin-left: 250px; padding: 32px; background: #f6f8fb; min-height: 100vh; }
        h2 { color: #ff8800; margin-top: 0; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 700px; margin: 0 auto 32px auto; }
        .form-row { display: flex; gap: 18px; margin-bottom: 16px; }
        .form-row input, .form-row select, .form-row textarea { flex: 1; }
        input, select, textarea { width: 100%; border-radius: 10px; border: 1.5px solid #eee; padding: 12px; font-size: 1rem; margin-bottom: 8px; background: #f6f8fb; transition: border 0.2s; font-family: inherit; }
        input:focus, select:focus, textarea:focus { border: 1.5px solid #ff8800; outline: none; }
        textarea { min-height: 60px; resize: vertical; }
        .submit-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 12px 32px; font-size: 1.1rem; font-weight: 700; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .submit-btn:hover { background: #e67600; }
        .msg-success { color: #15c26b; background: #e6f9ed; border-radius: 8px; padding: 10px 18px; margin-bottom: 16px; font-weight: 600; }
        .msg-error { color: #ff3333; background: #ffeaea; border-radius: 8px; padding: 10px 18px; margin-bottom: 16px; font-weight: 600; }
        .leave-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .leave-table th, .leave-table td { padding: 12px 10px; text-align: left; }
        .leave-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .leave-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .badge-pending { background: #fffbe6; color: #ff8800; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-approved { background: #e6f9ed; color: #15c26b; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-rejected { background: #ffeaea; color: #ff3333; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        @media (max-width: 900px) { .card { padding: 18px; } }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="employee_dashboard.php"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="apply_leave.php" class="active"><span class="icon" data-feather="calendar"></span>Apply for Leave</a></li>
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
        <h2>Apply for Leave</h2>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Leave Application</h3>
            <?php if ($success): ?><div class="msg-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="msg-error"><?php echo $error; ?></div><?php endif; ?>
            <form autocomplete="off">
                <div class="form-row">
                    <select name="type" required>
                        <option value="" disabled selected>Leave Type</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="date" name="from" placeholder="From" required>
                    <input type="date" name="to" placeholder="To" required>
                </div>
                <div class="form-row">
                    <textarea name="reason" placeholder="Reason" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Apply</button>
            </form>
        </div>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Recent Leave Requests</h3>
            <table class="leave-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $req): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($req['type']); ?></td>
                        <td><?php echo htmlspecialchars($req['from']); ?></td>
                        <td><?php echo htmlspecialchars($req['to']); ?></td>
                        <td><?php echo htmlspecialchars($req['reason']); ?></td>
                        <td>
                            <?php if ($req['status'] === 'Pending'): ?>
                                <span class="badge-pending">Pending</span>
                            <?php elseif ($req['status'] === 'Approved'): ?>
                                <span class="badge-approved">Approved</span>
                            <?php else: ?>
                                <span class="badge-rejected">Rejected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>feather.replace();</script>
</body>
</html> 