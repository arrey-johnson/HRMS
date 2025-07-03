<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
// Placeholder employees
$employees = [
    [
        'name' => 'John Doe',
        'department' => 'IT',
        'role' => 'EMPLOYEE',
        'status' => 'Active'
    ],
    [
        'name' => 'Jane Smith',
        'department' => 'HR',
        'role' => 'EMPLOYEE',
        'status' => 'Active'
    ],
    [
        'name' => 'Alice Brown',
        'department' => 'IT',
        'role' => 'EMPLOYEE',
        'status' => 'Inactive'
    ]
];
// Placeholder attendance records
$attendance_history = [
    [
        'date' => '2024-07-08',
        'employee' => 'John Doe',
        'status' => 'Present'
    ],
    [
        'date' => '2024-07-08',
        'employee' => 'Jane Smith',
        'status' => 'Remote'
    ],
    [
        'date' => '2024-07-07',
        'employee' => 'John Doe',
        'status' => 'Absent'
    ]
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Employee Records - HRMS</title>
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
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 1100px; margin: 0 auto 32px auto; }
        .employees-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .employees-table th, .employees-table td { padding: 12px 10px; text-align: left; }
        .employees-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .employees-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .badge-active { background: #e6f9ed; color: #15c26b; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-inactive { background: #ffeaea; color: #ff3333; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .action-btn { background: #ff8800; color: #fff; border: none; border-radius: 16px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-right: 8px; transition: background 0.2s; }
        .action-btn.inactive { background: #ff3333; }
        .action-btn:hover { background: #e67600; }
        .action-btn.inactive:hover { background: #c20000; }
        .attendance-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px 32px; margin: 0 auto 32px auto; max-width: 900px; }
        .attendance-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .attendance-table th, .attendance-table td { padding: 10px 8px; text-align: left; }
        .attendance-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .attendance-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .save-attendance-btn { background: #ff8800; color: #fff; border: none; border-radius: 12px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .save-attendance-btn:hover { background: #e67600; }
        .success-msg { color: #15c26b; font-size: 0.98rem; margin-left: 8px; display: none; }
        @media (max-width: 900px) { .card, .attendance-card { padding: 18px; } }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="hr_dashboard.php"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="manage_employees.php" class="active"><span class="icon" data-feather="users"></span>Manage employee records</a></li>
                <li><a href="post_jobs.php"><span class="icon" data-feather="briefcase"></span>Post job openings</a></li>
                <li><a href="manage_leave.php"><span class="icon" data-feather="calendar"></span>Approve/reject leave</a></li>
                <li><a href="monitor_performance.php"><span class="icon" data-feather="bar-chart-2"></span>Monitor performance</a></li>
                <li><a href="intern_onboarding.php"><span class="icon" data-feather="user-check"></span>Manage intern onboarding</a></li>
                <li><a href="assign_interns.php"><span class="icon" data-feather="user-plus"></span>Assign interns</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="main">
        <h2>Manage Employee Records</h2>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Employee List</h3>
            <table class="employees-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $i => $emp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emp['name']); ?></td>
                        <td><?php echo htmlspecialchars($emp['department']); ?></td>
                        <td><?php echo htmlspecialchars($emp['role']); ?></td>
                        <td>
                            <?php if ($emp['status'] === 'Active'): ?>
                                <span class="badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="action-btn" onclick="openProfileModal(<?php echo $i; ?>)">View Profile</button>
                            <?php if ($emp['status'] === 'Active'): ?>
                                <button class="action-btn inactive" onclick="alert('Marked as inactive!')">Mark Inactive</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Modal for viewing/editing employee profile -->
        <div class="modal-bg" id="profileModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.25); z-index:1000; align-items:center; justify-content:center;">
            <div class="modal-card" style="max-width:400px; width:100%; position:relative;">
                <button class="modal-close" onclick="closeProfileModal()" style="background:none; border:none; color:#ff8800; font-size:1.3rem; position:absolute; top:18px; right:24px; cursor:pointer;">&times;</button>
                <h3 style="color:#ff8800; margin-top:0;">Employee Profile</h3>
                <div id="profileDetails"></div>
            </div>
        </div>
        <div class="attendance-card">
            <h3 style="color:#ff8800;">Register Attendance for Today</h3>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $i => $emp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emp['name']); ?></td>
                        <td>
                            <select class="attendance-select" id="attendance_<?php echo $i; ?>">
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Remote">Remote</option>
                            </select>
                        </td>
                        <td>
                            <button class="save-attendance-btn" onclick="saveAttendance(<?php echo $i; ?>)">Save</button>
                            <span class="success-msg" id="success_<?php echo $i; ?>">Saved!</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Recent Attendance Records</h3>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_history as $rec): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rec['date']); ?></td>
                        <td><?php echo htmlspecialchars($rec['employee']); ?></td>
                        <td><?php echo htmlspecialchars($rec['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        feather.replace();
        // Profile modal logic
        const employees = <?php echo json_encode($employees); ?>;
        function openProfileModal(idx) {
            const emp = employees[idx];
            let html = `<div><b>Name:</b> ${emp.name}</div>`;
            html += `<div><b>Department:</b> ${emp.department}</div>`;
            html += `<div><b>Role:</b> ${emp.role}</div>`;
            html += `<div><b>Status:</b> ${emp.status}</div>`;
            document.getElementById('profileDetails').innerHTML = html;
            document.getElementById('profileModal').style.display = 'flex';
        }
        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
        }
        // Attendance save logic (demo only)
        function saveAttendance(idx) {
            document.getElementById('success_' + idx).style.display = 'inline';
            setTimeout(() => {
                document.getElementById('success_' + idx).style.display = 'none';
            }, 1200);
        }
    </script>
</body>
</html> 