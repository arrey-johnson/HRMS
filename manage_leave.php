<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
// Placeholder leave requests
$leave_requests = [
    [
        'id' => 1,
        'employee' => 'John Doe',
        'role' => 'EMPLOYEE',
        'type' => 'Sick Leave',
        'from' => '2024-07-10',
        'to' => '2024-07-12',
        'reason' => 'Fever',
        'status' => 'Pending'
    ],
    [
        'id' => 2,
        'employee' => 'Jane Smith',
        'role' => 'EMPLOYEE',
        'type' => 'Annual Leave',
        'from' => '2024-07-15',
        'to' => '2024-07-20',
        'reason' => 'Vacation',
        'status' => 'Pending'
    ]
];
// Placeholder attendance records
$attendance_records = [
    [
        'date' => '2024-07-08',
        'employee' => 'John Doe',
        'role' => 'EMPLOYEE',
        'status' => 'Present'
    ],
    [
        'date' => '2024-07-08',
        'employee' => 'Jane Smith',
        'role' => 'EMPLOYEE',
        'status' => 'Remote'
    ],
    [
        'date' => '2024-07-07',
        'employee' => 'John Doe',
        'role' => 'EMPLOYEE',
        'status' => 'Absent'
    ]
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approve/Reject Leave & Attendance - HRMS</title>
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
        .tabs { display: flex; gap: 18px; margin-bottom: 24px; }
        .tab-btn { background: #fff2e0; color: #ff8800; border: none; border-radius: 20px 20px 0 0; padding: 12px 32px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: background 0.2s, color 0.2s; }
        .tab-btn.active, .tab-btn:hover { background: #ff8800; color: #fff; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 1000px; margin: 0 auto 32px auto; }
        .leave-table, .attendance-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .leave-table th, .leave-table td, .attendance-table th, .attendance-table td { padding: 12px 10px; text-align: left; }
        .leave-table th, .attendance-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .leave-table td, .attendance-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .badge-pending { background: #fffbe6; color: #ff8800; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-approved { background: #e6f9ed; color: #15c26b; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-rejected { background: #ffeaea; color: #ff3333; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .action-btn { background: #ff8800; color: #fff; border: none; border-radius: 16px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-right: 8px; transition: background 0.2s; }
        .action-btn.reject { background: #ff3333; }
        .action-btn:hover { background: #e67600; }
        .action-btn.reject:hover { background: #c20000; }
        .search-bar { margin-bottom: 18px; display: flex; gap: 10px; }
        .search-bar input { border-radius: 10px; border: 1.5px solid #eee; padding: 10px 16px; font-size: 1rem; background: #f6f8fb; transition: border 0.2s; font-family: inherit; }
        .search-bar input:focus { border: 1.5px solid #ff8800; outline: none; }
        @media (max-width: 900px) { .card { padding: 18px; } }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="hr_dashboard.php"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="manage_employees.php"><span class="icon" data-feather="users"></span>Manage employee records</a></li>
                <li><a href="post_jobs.php"><span class="icon" data-feather="briefcase"></span>Post job openings</a></li>
                <li><a href="manage_leave.php" class="active"><span class="icon" data-feather="calendar"></span>Approve/reject leave</a></li>
                <li><a href="monitor_performance.php"><span class="icon" data-feather="bar-chart-2"></span>Monitor performance</a></li>
                <li><a href="intern_onboarding.php"><span class="icon" data-feather="user-check"></span>Manage intern onboarding</a></li>
                <li><a href="assign_interns.php"><span class="icon" data-feather="user-plus"></span>Assign interns</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="main">
        <h2>Approve/Reject Leave & Attendance</h2>
        <div class="tabs">
            <button class="tab-btn active" id="leaveTabBtn" onclick="showTab('leave')">Pending Leave Requests</button>
            <button class="tab-btn" id="attendanceTabBtn" onclick="showTab('attendance')">Attendance Records</button>
        </div>
        <div class="card" id="leaveTab">
            <h3 style="color:#ff8800; margin-top:0;">Pending Leave Requests</h3>
            <table class="leave-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $req): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($req['employee']); ?></td>
                        <td><?php echo htmlspecialchars($req['role']); ?></td>
                        <td><?php echo htmlspecialchars($req['type']); ?></td>
                        <td><?php echo htmlspecialchars($req['from']); ?></td>
                        <td><?php echo htmlspecialchars($req['to']); ?></td>
                        <td><?php echo htmlspecialchars($req['reason']); ?></td>
                        <td><span class="badge-pending">Pending</span></td>
                        <td>
                            <button class="action-btn" onclick="alert('Approved!')">Approve</button>
                            <button class="action-btn reject" onclick="alert('Rejected!')">Reject</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card" id="attendanceTab" style="display:none;">
            <h3 style="color:#ff8800; margin-top:0;">Attendance Records</h3>
            <div class="search-bar">
                <input type="text" id="attendanceSearch" placeholder="Search by employee name..." onkeyup="filterAttendance()">
            </div>
            <table class="attendance-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $rec): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rec['date']); ?></td>
                        <td><?php echo htmlspecialchars($rec['employee']); ?></td>
                        <td><?php echo htmlspecialchars($rec['role']); ?></td>
                        <td><?php echo htmlspecialchars($rec['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        feather.replace();
        function showTab(tab) {
            document.getElementById('leaveTab').style.display = (tab === 'leave') ? '' : 'none';
            document.getElementById('attendanceTab').style.display = (tab === 'attendance') ? '' : 'none';
            document.getElementById('leaveTabBtn').classList.toggle('active', tab === 'leave');
            document.getElementById('attendanceTabBtn').classList.toggle('active', tab === 'attendance');
        }
        function filterAttendance() {
            var input = document.getElementById('attendanceSearch').value.toLowerCase();
            var rows = document.querySelectorAll('#attendanceTable tbody tr');
            rows.forEach(function(row) {
                var name = row.cells[1].textContent.toLowerCase();
                row.style.display = name.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>
</html> 