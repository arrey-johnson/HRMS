<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
// Placeholder mentors
$mentors = ['Alice Brown', 'Bob Green', 'Jane Smith'];
// Placeholder interns
$interns = [
    [
        'name' => 'Tom Lee',
        'email' => 'tom.lee@email.com',
        'department' => 'IT',
        'mentor' => 'Alice Brown',
        'start' => '2024-07-01',
        'end' => '2024-09-30',
        'status' => 'Ongoing'
    ],
    [
        'name' => 'Sara White',
        'email' => 'sara.white@email.com',
        'department' => 'HR',
        'mentor' => 'Jane Smith',
        'start' => '2024-06-15',
        'end' => '2024-08-15',
        'status' => 'Ongoing'
    ],
    [
        'name' => 'Mike Black',
        'email' => 'mike.black@email.com',
        'department' => 'Marketing',
        'mentor' => 'Bob Green',
        'start' => '2024-05-10',
        'end' => '2024-07-10',
        'status' => 'Completed'
    ]
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Intern Onboarding - HRMS</title>
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
        .form-row { display: flex; gap: 18px; margin-bottom: 16px; }
        .form-row input, .form-row select { flex: 1; }
        input, select { width: 100%; border-radius: 10px; border: 1.5px solid #eee; padding: 12px; font-size: 1rem; margin-bottom: 8px; background: #f6f8fb; transition: border 0.2s; font-family: inherit; }
        input:focus, select:focus { border: 1.5px solid #ff8800; outline: none; }
        .submit-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 12px 32px; font-size: 1.1rem; font-weight: 700; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .submit-btn:hover { background: #e67600; }
        .interns-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .interns-table th, .interns-table td { padding: 12px 10px; text-align: left; }
        .interns-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .interns-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .badge-ongoing { background: #fffbe6; color: #ff8800; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-completed { background: #e6f9ed; color: #15c26b; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .action-btn { background: #ff8800; color: #fff; border: none; border-radius: 16px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-right: 8px; transition: background 0.2s; }
        .action-btn:hover { background: #e67600; }
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
                <li><a href="manage_leave.php"><span class="icon" data-feather="calendar"></span>Approve/reject leave</a></li>
                <li><a href="monitor_performance.php"><span class="icon" data-feather="bar-chart-2"></span>Monitor performance</a></li>
                <li><a href="intern_onboarding.php" class="active"><span class="icon" data-feather="user-check"></span>Manage intern onboarding</a></li>
                <li><a href="assign_interns.php"><span class="icon" data-feather="user-plus"></span>Assign interns</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="main">
        <h2>Manage Intern Onboarding</h2>
        <div class="tabs">
            <button class="tab-btn active" id="onboardTabBtn" onclick="showTab('onboard')">Onboard New Intern</button>
            <button class="tab-btn" id="currentTabBtn" onclick="showTab('current')">Current Interns</button>
        </div>
        <div class="card" id="onboardTab">
            <h3 style="color:#ff8800; margin-top:0;">Onboard New Intern</h3>
            <form autocomplete="off">
                <div class="form-row">
                    <input type="text" name="name" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-row">
                    <input type="text" name="department" placeholder="Department" required>
                    <select name="mentor" required>
                        <option value="" disabled selected>Select Mentor</option>
                        <?php foreach ($mentors as $mentor): ?>
                        <option value="<?php echo htmlspecialchars($mentor); ?>"><?php echo htmlspecialchars($mentor); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <input type="date" name="start" placeholder="Start Date" required>
                    <input type="date" name="end" placeholder="End Date" required>
                </div>
                <button type="submit" class="submit-btn">Onboard Intern</button>
            </form>
        </div>
        <div class="card" id="currentTab" style="display:none;">
            <h3 style="color:#ff8800; margin-top:0;">Current Interns</h3>
            <table class="interns-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Mentor</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interns as $intern): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($intern['name']); ?></td>
                        <td><?php echo htmlspecialchars($intern['email']); ?></td>
                        <td><?php echo htmlspecialchars($intern['department']); ?></td>
                        <td><?php echo htmlspecialchars($intern['mentor']); ?></td>
                        <td><?php echo htmlspecialchars($intern['start']); ?></td>
                        <td><?php echo htmlspecialchars($intern['end']); ?></td>
                        <td>
                            <?php if ($intern['status'] === 'Ongoing'): ?>
                                <span class="badge-ongoing">Ongoing</span>
                            <?php else: ?>
                                <span class="badge-completed">Completed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($intern['status'] === 'Ongoing'): ?>
                                <button class="action-btn" onclick="alert('Marked as completed!')">Mark as Completed</button>
                            <?php else: ?>
                                <span style="color:#aaa;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        feather.replace();
        function showTab(tab) {
            document.getElementById('onboardTab').style.display = (tab === 'onboard') ? '' : 'none';
            document.getElementById('currentTab').style.display = (tab === 'current') ? '' : 'none';
            document.getElementById('onboardTabBtn').classList.toggle('active', tab === 'onboard');
            document.getElementById('currentTabBtn').classList.toggle('active', tab === 'current');
        }
    </script>
</body>
</html> 