<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
// Placeholder KPIs
$kpis = [
    ['label' => 'Avg. Performance Score', 'value' => '87', 'icon' => 'trending-up'],
    ['label' => 'Employees Exceeding Goals', 'value' => '12', 'icon' => 'award'],
    ['label' => 'Pending Appraisals', 'value' => '3', 'icon' => 'clock'],
    ['label' => 'Completed Appraisals', 'value' => '28', 'icon' => 'check-circle']
];
// Placeholder appraisal records
$appraisals = [
    [
        'employee' => 'John Doe',
        'period' => 'Q2 2024',
        'score' => '92',
        'status' => 'Completed'
    ],
    [
        'employee' => 'Jane Smith',
        'period' => 'Q2 2024',
        'score' => '85',
        'status' => 'Completed'
    ],
    [
        'employee' => 'Alice Brown',
        'period' => 'Q2 2024',
        'score' => '78',
        'status' => 'Pending'
    ]
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Monitor Performance & Appraisals - HRMS</title>
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
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 1100px; margin: 0 auto 32px auto; }
        .kpi-row { display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap; }
        .kpi-card { background: #fff2e0; border-radius: 14px; padding: 24px 32px; flex: 1; min-width: 180px; display: flex; align-items: center; gap: 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .kpi-icon { background: #ff8800; color: #fff; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .kpi-label { color: #888; font-size: 1rem; font-weight: 600; }
        .kpi-value { font-size: 2.1rem; color: #222; font-weight: 700; }
        .chart-container { width: 100%; max-width: 600px; margin: 0 auto 32px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px; }
        .appraisal-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .appraisal-table th, .appraisal-table td { padding: 12px 10px; text-align: left; }
        .appraisal-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .appraisal-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .badge-completed { background: #e6f9ed; color: #15c26b; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-pending { background: #fffbe6; color: #ff8800; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .action-btn { background: #ff8800; color: #fff; border: none; border-radius: 16px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-right: 8px; transition: background 0.2s; }
        .action-btn:hover { background: #e67600; }
        @media (max-width: 900px) { .card { padding: 18px; } .kpi-row { flex-direction: column; gap: 12px; } }
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
                <li><a href="monitor_performance.php" class="active"><span class="icon" data-feather="bar-chart-2"></span>Monitor performance</a></li>
                <li><a href="intern_onboarding.php"><span class="icon" data-feather="user-check"></span>Manage intern onboarding</a></li>
                <li><a href="assign_interns.php"><span class="icon" data-feather="user-plus"></span>Assign interns</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="main">
        <h2>Monitor Performance & Appraisals</h2>
        <div class="tabs">
            <button class="tab-btn active" id="overviewTabBtn" onclick="showTab('overview')">Performance Overview</button>
            <button class="tab-btn" id="appraisalTabBtn" onclick="showTab('appraisal')">Appraisal Records</button>
        </div>
        <div class="card" id="overviewTab">
            <h3 style="color:#ff8800; margin-top:0;">Performance Overview</h3>
            <div class="kpi-row">
                <?php foreach ($kpis as $kpi): ?>
                <div class="kpi-card">
                    <div class="kpi-icon"><i data-feather="<?php echo $kpi['icon']; ?>"></i></div>
                    <div>
                        <div class="kpi-label"><?php echo htmlspecialchars($kpi['label']); ?></div>
                        <div class="kpi-value"><?php echo htmlspecialchars($kpi['value']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="chart-container">
                <img src="https://quickchart.io/chart?c=%7B%22type%22%3A%22bar%22%2C%22data%22%3A%7B%22labels%22%3A%5B%22John%22%2C%22Jane%22%2C%22Alice%22%2C%22Bob%22%5D%2C%22datasets%22%3A%5B%7B%22label%22%3A%22Performance%22%2C%22data%22%3A%5B92%2C85%2C78%2C88%5D%2C%22backgroundColor%22%3A%22%23ff8800%22%7D%5D%7D%7D" alt="Performance Chart" style="width:100%;max-width:500px;display:block;margin:0 auto;">
            </div>
        </div>
        <div class="card" id="appraisalTab" style="display:none;">
            <h3 style="color:#ff8800; margin-top:0;">Appraisal Records</h3>
            <table class="appraisal-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appraisals as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($app['employee']); ?></td>
                        <td><?php echo htmlspecialchars($app['period']); ?></td>
                        <td><?php echo htmlspecialchars($app['score']); ?></td>
                        <td>
                            <?php if ($app['status'] === 'Completed'): ?>
                                <span class="badge-completed">Completed</span>
                            <?php else: ?>
                                <span class="badge-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($app['status'] === 'Pending'): ?>
                                <button class="action-btn" onclick="alert('Appraisal completed!')">Complete</button>
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
            document.getElementById('overviewTab').style.display = (tab === 'overview') ? '' : 'none';
            document.getElementById('appraisalTab').style.display = (tab === 'appraisal') ? '' : 'none';
            document.getElementById('overviewTabBtn').classList.toggle('active', tab === 'overview');
            document.getElementById('appraisalTabBtn').classList.toggle('active', tab === 'appraisal');
        }
    </script>
</body>
</html> 