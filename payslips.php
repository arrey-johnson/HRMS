<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMPLOYEE') {
    header('Location: index.php');
    exit();
}
$employee_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Employee';
// Placeholder payslips
$payslips = [
    ['month' => 'July 2024', 'amount' => 3200, 'status' => 'Paid', 'file' => '#'],
    ['month' => 'June 2024', 'amount' => 3200, 'status' => 'Paid', 'file' => '#'],
    ['month' => 'May 2024', 'amount' => 3200, 'status' => 'Paid', 'file' => '#'],
    ['month' => 'April 2024', 'amount' => 3200, 'status' => 'Paid', 'file' => '#'],
    ['month' => 'March 2024', 'amount' => 3200, 'status' => 'Paid', 'file' => '#'],
];
$total_earnings = 3200 * 5;
// Salary history for chart
$salary_months = ['Mar', 'Apr', 'May', 'Jun', 'Jul'];
$salary_amounts = [3200, 3200, 3200, 3200, 3200];
$chart_url = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
    'type' => 'bar',
    'data' => [
        'labels' => $salary_months,
        'datasets' => [[
            'label' => 'Salary',
            'data' => $salary_amounts,
            'backgroundColor' => '#ff8800'
        ]]
    ]
]));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payslips & Salary History - HRMS</title>
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
        .dashboard-row { display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; flex: 1; min-width: 220px; margin-bottom: 24px; }
        .summary-card { background: #fff2e0; border-radius: 14px; padding: 24px 32px; min-width: 180px; display: flex; align-items: center; gap: 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); font-size: 1.2rem; font-weight: 700; color: #ff8800; margin-bottom: 24px; }
        .payslips-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .payslips-table th, .payslips-table td { padding: 12px 10px; text-align: left; }
        .payslips-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .payslips-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .badge-paid { background: #e6f9ed; color: #15c26b; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-unpaid { background: #ffeaea; color: #ff3333; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .download-btn { background: #ff8800; color: #fff; border: none; border-radius: 12px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; text-decoration: none; }
        .download-btn:hover { background: #e67600; }
        .chart-container { width: 100%; max-width: 600px; margin: 0 auto 32px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px; }
        @media (max-width: 900px) { .card, .summary-card, .chart-container { padding: 18px; } .dashboard-row { flex-direction: column; gap: 12px; } }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="employee_dashboard.php"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="apply_leave.php"><span class="icon" data-feather="calendar"></span>Apply for Leave</a></li>
                <li><a href="payslips.php" class="active"><span class="icon" data-feather="file-text"></span>View Payslips</a></li>
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
        <h2>Payslips & Salary History</h2>
        <div class="summary-card">
            <span>Total Earnings (2024):</span> <?php echo number_format($total_earnings, 0, '', ' '); ?> CFA
        </div>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Recent Payslips</h3>
            <table class="payslips-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payslips as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['month']); ?></td>
                        <td><?php echo number_format($p['amount'], 0, '', ' '); ?> CFA</td>
                        <td>
                            <?php if ($p['status'] === 'Paid'): ?>
                                <span class="badge-paid">Paid</span>
                            <?php else: ?>
                                <span class="badge-unpaid">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="<?php echo htmlspecialchars($p['file']); ?>" class="download-btn"><span data-feather="download"></span> Download</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="chart-container">
            <h3 style="color:#ff8800; margin-top:0;">Salary History</h3>
            <img src="<?php echo $chart_url; ?>" alt="Salary Chart" style="width:100%;max-width:500px;display:block;margin:0 auto;">
        </div>
    </div>
    <script>feather.replace();</script>
</body>
</html> 