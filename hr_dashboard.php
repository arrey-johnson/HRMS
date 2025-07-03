<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>HR Manager Dashboard - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <style>
        body { background: #f6f8fb; font-family: 'Inter', Arial, sans-serif; margin: 0; color: #333; }
        .sidebar { width: 240px; background: #fff; color: #ff8800; height: 100vh; position: fixed; left: 0; top: 0; padding: 0; display: flex; flex-direction: column; z-index: 2; border-right: 1.5px solid #ffe0b3; }
        .sidebar .brand { display: flex; align-items: center; justify-content: center; height: 70px; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; background: #fff; color: #ff8800; border-bottom: 1px solid #ffe0b3; }
        .sidebar nav { flex: 1; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { }
        .sidebar a { display: flex; align-items: center; gap: 14px; color: #888; text-decoration: none; padding: 16px 32px; font-size: 1.08rem; font-weight: 500; transition: background 0.2s, color 0.2s; border-left: 4px solid transparent; }
        .sidebar a.active, .sidebar a:hover { background: #fff2e0; color: #ff8800; border-left: 4px solid #ff8800; }
        .sidebar a .icon { width: 20px; height: 20px; color: #ff8800; }
        .sidebar .logout { margin: 24px 32px 32px 32px; color: #fff; background: #ff8800; border-radius: 8px; padding: 10px 0; text-align: center; display: block; font-weight: 600; text-decoration: none; transition: background 0.2s, color 0.2s; }
        .sidebar .logout:hover { background: #fff; color: #ff8800; border: 1.5px solid #ff8800; }
        .main-area { margin-left: 240px; min-height: 100vh; background: #f6f8fb; display: flex; flex-direction: row; }
        .dashboard-content { flex: 1; padding: 36px 24px 24px 24px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .topbar .greeting { font-size: 1.5rem; font-weight: 700; color: #222; }
        .topbar .search-box { display: flex; align-items: center; background: #f6f8fb; border-radius: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 4px 12px; border: 1.5px solid #eee; }
        .topbar .search-box input[type="text"] { border: none; background: transparent; outline: none; font-size: 1rem; padding: 8px 8px; min-width: 180px; }
        .topbar .search-box button { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 8px 18px; font-size: 1rem; font-weight: 600; margin-left: 8px; cursor: pointer; transition: background 0.2s; }
        .topbar .search-box button:hover { background: #e67600; }
        .topbar .actions { display: flex; align-items: center; gap: 18px; }
        .topbar .post-job-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 10px 28px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .topbar .post-job-btn:hover { background: #e67600; }
        .topbar .avatar { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ff8800; }
        .stats-row { display: flex; gap: 24px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px 32px; flex: 1; display: flex; flex-direction: column; align-items: flex-start; min-width: 120px; }
        .stat-card .stat-label { color: #888; font-size: 1rem; font-weight: 600; margin-bottom: 8px; }
        .stat-card .stat-value { font-size: 2.1rem; color: #222; font-weight: 700; }
        .stat-card .stat-change { font-size: 1rem; font-weight: 600; margin-left: 8px; }
        .stat-up { color: #15c26b; }
        .stat-down { color: #ff3333; }
        .dashboard-grid { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .dashboard-grid .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px 24px 18px 24px; min-width: 0; }
        .applications-card {
            box-shadow: 0 4px 24px rgba(255,136,0,0.10);
            border-radius: 18px;
            padding: 28px 28px 18px 28px;
            background: #fff;
            min-width: 0;
            overflow-x: auto;
            max-width: 100%;
        }
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            max-width: 100%;
            word-break: break-word;
        }
        .applications-table th, .applications-table td {
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .applications-table th { color: #ff8800; background: #fff2e0; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .applications-table td { border-bottom: 1px solid #f0f0f0; }
        .resource-status { display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .resource-status .circle-chart { width: 110px; height: 110px; margin-bottom: 10px; }
        .resource-status .legend { display: flex; gap: 12px; margin-top: 10px; }
        .resource-status .legend-item { display: flex; align-items: center; gap: 6px; font-size: 0.98rem; }
        .resource-status .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
        .legend-dot.present { background: #15c26b; }
        .legend-dot.remote { background: #ff8800; }
        .legend-dot.absent { background: #ff3333; }
        .job-stats { width: 100%; height: 180px; }
        .right-panel { width: 320px; background: transparent; padding: 36px 24px 24px 0; }
        .interviews-card, .calendar-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 22px 22px 18px 22px; margin-bottom: 24px; }
        .interviews-card h4, .calendar-card h4 { margin: 0 0 14px 0; color: #ff8800; font-size: 1.1rem; font-weight: 700; }
        .interview-list { list-style: none; padding: 0; margin: 0; }
        .interview-list li { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .interview-avatar { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #eee; }
        .interview-info { flex: 1; }
        .interview-name { font-weight: 600; color: #222; }
        .interview-role { font-size: 0.98rem; color: #888; }
        .interview-time { font-size: 0.98rem; color: #888; font-weight: 600; }
        .add-interview-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .add-interview-btn:hover { background: #e67600; }
        .calendar-card { overflow-x: auto; }
        .calendar-table { width: 100%; border-collapse: collapse; text-align: left; }
        .calendar-table th, .calendar-table td { padding: 8px 10px; font-size: 1rem; }
        .calendar-table th { color: #ff8800; font-weight: 700; background: #fff2e0; }
        .calendar-table td { color: #333; border-radius: 6px; border-bottom: 1px solid #f0f0f0; }
        .calendar-table td.today { background: #ff8800; color: #fff; font-weight: 700; }
        @media (max-width: 1100px) { .main-area { flex-direction: column; } .right-panel { width: 100%; padding: 0; } }
        @media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } .stats-row { flex-direction: column; } }
        @media (max-width: 700px) { .sidebar, .topbar { display: none; } .main-area { margin-left: 0; padding: 8px; flex-direction: column; } .dashboard-content { padding: 8px; } }
        .topnav { height: 64px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 1; }
        .topnav .search-area { flex: 1; display: flex; justify-content: center; }
        .search-box { display: flex; align-items: center; background: #f6f8fb; border-radius: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 4px 12px; }
        .search-box input[type="text"] { border: none; background: transparent; outline: none; font-size: 1rem; padding: 8px 8px; min-width: 220px; }
        .search-box button { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 8px 18px; font-size: 1rem; font-weight: 600; margin-left: 8px; cursor: pointer; transition: background 0.2s; }
        .search-box button:hover { background: #e67600; }
        .topnav .profile { display: flex; align-items: center; gap: 18px; position: relative; margin-left: auto; }
        .topnav .avatar { width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ff8800; }
        .applications-card, .resource-status-card {
            box-shadow: 0 4px 24px rgba(255,136,0,0.10);
            border-radius: 18px;
            padding: 28px 28px 18px 28px;
            background: #fff;
            min-width: 0;
        }
        .card-header { margin-bottom: 10px; }
        .badge {
            display: inline-block;
            border-radius: 12px;
            padding: 2px 12px;
            font-size: 0.98rem;
            font-weight: 600;
            margin-right: 6px;
        }
        .badge-received { background: #e6f9ed; color: #15c26b; }
        .badge-hold { background: #fffbe6; color: #ff8800; }
        .badge-rejected { background: #ffeaea; color: #ff3333; }
        .resource-status-card .circle-chart { margin-bottom: 0; }
        .resource-status-card .legend-dot.present { background: #15c26b; }
        .resource-status-card .legend-dot.remote { background: #ff8800; }
        .resource-status-card .legend-dot.absent { background: #ff3333; }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
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
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="hr_dashboard.php" class="active"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="manage_employees.php"><span class="icon" data-feather="users"></span>Manage employee records</a></li>
                <li><a href="post_jobs.php"><span class="icon" data-feather="briefcase"></span>Post job openings</a></li>
                <li><a href="manage_leave.php"><span class="icon" data-feather="calendar"></span>Approve/reject leave</a></li>
                <li><a href="monitor_performance.php"><span class="icon" data-feather="bar-chart-2"></span>Monitor performance</a></li>
                <li><a href="intern_onboarding.php"><span class="icon" data-feather="user-check"></span>Manage intern onboarding</a></li>
                <li><a href="assign_interns.php"><span class="icon" data-feather="user-plus"></span>Assign interns</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="main-area" style="display:flex;">
        <div class="dashboard-content" style="flex:2;">
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-label">Interview</div>
                    <div class="stat-value">256 <span class="stat-change stat-up">+20%</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Shortlisted</div>
                    <div class="stat-value">20 <span class="stat-change stat-up">+5%</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Hired</div>
                    <div class="stat-value">6 <span class="stat-change stat-down">-5%</span></div>
                </div>
            </div>
            <div style="display:flex; gap:24px; margin-bottom:24px;">
                <div class="card resource-status-card" style="flex:1; align-items:center;">
                    <div class="card-header" style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                        <span data-feather="pie-chart" style="color:#ff8800;"></span>
                        <span style="font-weight:700; font-size:1.1rem; color:#222;">Resource Status</span>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:100%;">
                        <img src="https://quickchart.io/chart?c=%7B%22type%22%3A%22pie%22%2C%22data%22%3A%7B%22labels%22%3A%5B%22Present%22%2C%22Remote%22%2C%22Absent%22%5D%2C%22datasets%22%3A%5B%7B%22data%22%3A%5B250%2C4%2C2%5D%2C%22backgroundColor%22%3A%5B%22%2315c26b%22%2C%22%23ff8800%22%2C%22%23ff3333%22%5D%7D%5D%7D%7D" alt="Resource Status Pie Chart" style="width:160px;max-width:100%;margin-bottom:0;">
                        <div style="font-size:1.1rem; font-weight:600; color:#888; margin-top:8px;">Total Emp</div>
                        <div class="legend" style="margin-top:12px;">
                            <div class="legend-item"><span class="legend-dot present"></span>Present: 250</div>
                            <div class="legend-item"><span class="legend-dot remote"></span>Remote: 04</div>
                            <div class="legend-item"><span class="legend-dot absent"></span>Absent: 02</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-bottom:24px;">
                <div style="font-weight:600; color:#222; margin-bottom:10px;">Job Stats</div>
                <img src="https://quickchart.io/chart?c=%7B%22type%22%3A%22bar%22%2C%22data%22%3A%7B%22labels%22%3A%5B%22Mon%22%2C%22Tue%22%2C%22Wed%22%2C%22Thu%22%2C%22Fri%22%2C%22Sat%22%2C%22Sun%22%5D%2C%22datasets%22%3A%5B%7B%22label%22%3A%22Job%20Views%22%2C%22data%22%3A%5B12%2C19%2C13%2C17%2C22%2C18%2C14%5D%2C%22backgroundColor%22%3A%22%23ff8800%22%7D%2C%7B%22label%22%3A%22Job%20Applied%22%2C%22data%22%3A%5B7%2C11%2C8%2C10%2C15%2C12%2C9%5D%2C%22backgroundColor%22%3A%22%2315c26b%22%7D%5D%7D%7D" alt="Job Stats" class="job-stats">
            </div>
        </div>
        <div class="right-panel" style="flex:1; min-width:320px;">
            <div class="interviews-card">
                <h4>Upcoming Interviews</h4>
                <ul class="interview-list">
                    <li><img src="https://ui-avatars.com/api/?name=John+Doe&background=ff8800&color=fff" class="interview-avatar"><div class="interview-info"><div class="interview-name">John Doe</div><div class="interview-role">Software Engineer</div></div><div class="interview-time">10:00 AM</div></li>
                    <li><img src="https://ui-avatars.com/api/?name=Jane+Smith&background=ff8800&color=fff" class="interview-avatar"><div class="interview-info"><div class="interview-name">Jane Smith</div><div class="interview-role">UX Designer</div></div><div class="interview-time">11:00 AM</div></li>
                </ul>
                <button class="add-interview-btn">+ Add Interview</button>
            </div>
            <div class="calendar-card">
                <h4>Interview Calendar</h4>
                <table class="calendar-table" style="width:100%; table-layout:fixed;">
                    <thead>
                        <tr><th style="width:40%;">Day</th><th>Event</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Mon</td><td>Interview</td></tr>
                        <tr><td>Tue</td><td>Meeting</td></tr>
                        <tr><td>Wed</td><td>Training</td></tr>
                        <tr><td>Thu</td><td>Interview</td></tr>
                        <tr><td>Fri</td><td>Meeting</td></tr>
                        <tr><td>Sat</td><td>Training</td></tr>
                        <tr><td>Sun</td><td>Interview</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>feather.replace();</script>
</body>
</html> 