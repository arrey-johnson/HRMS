<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: index.php');
    exit();
}
require 'db.php';

// Number of staff
$staff_count = 0;
$role_counts = [
    'ADMIN' => 0,
    'HR MANAGER' => 0,
    'EMPLOYEE' => 0,
    'INTERN' => 0
];
$gender_counts = [
    'Male' => 0,
    'Female' => 0,
    'Other' => 0
];

$result = $conn->query("SELECT role FROM users");
if ($result) {
    $staff_count = $result->num_rows;
    while ($row = $result->fetch_assoc()) {
        $role = strtoupper($row['role']);
        if (isset($role_counts[$role])) {
            $role_counts[$role]++;
        }
    }
}
// Gender logic (if gender field exists)
if ($conn->query("SHOW COLUMNS FROM users LIKE 'gender'" )->num_rows) {
    $result = $conn->query("SELECT gender FROM users");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $g = ucfirst(strtolower($row['gender']));
            if (isset($gender_counts[$g])) {
                $gender_counts[$g]++;
            } else {
                $gender_counts['Other']++;
            }
        }
    }
}
// Placeholders for other metrics
$on_leave = 0; // Needs leave table
$profile_update_requests = 0; // Needs profile_update_requests table
$next_pay_date = '25th Jun'; // Static or from settings
$happiness = 72; // Placeholder
$leave_requests = 0; // Needs leave table
$loan_requests = 0; // Needs loan table
$other_requests = 0; // Needs other requests
$turnover = 15; // Placeholder
$retention = 85; // Placeholder
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - HRMS</title>
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
        .topnav .tabs { display: flex; gap: 32px; }
        .topnav .tab { color: #888; font-weight: 600; text-decoration: none; padding: 8px 0; border-bottom: 2px solid transparent; transition: color 0.2s, border 0.2s; }
        .topnav .tab.active { color: #ff8800; border-bottom: 2px solid #ff8800; }
        .topnav .profile { display: flex; align-items: center; gap: 18px; position: relative; margin-left: auto; }
        .topnav .avatar { width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ff8800; }
        .topnav .dropdown { display: none; position: absolute; right: 0; top: 48px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; min-width: 160px; }
        .topnav .profile:hover .dropdown { display: block; }
        .topnav .dropdown a { display: block; padding: 12px 18px; color: #333; text-decoration: none; border-bottom: 1px solid #f0f0f0; }
        .topnav .dropdown a:last-child { border-bottom: none; }
        .topnav .dropdown a:hover { background: #ff8800; color: #fff; }
        .main { margin-left: 250px; padding: 32px; background: #f6f8fb; min-height: 100vh; }
        .dashboard-cards { display: flex; gap: 24px; margin-bottom: 32px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 28px; flex: 1; min-width: 180px; display: flex; flex-direction: column; align-items: flex-start; transition: box-shadow 0.2s, transform 0.2s; position: relative; }
        .card:hover { box-shadow: 0 4px 24px rgba(255,136,0,0.12); transform: translateY(-2px) scale(1.01); }
        .card .card-title { color: #888; font-size: 1rem; margin-bottom: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .card .card-value { font-size: 2.1rem; color: #ff8800; font-weight: 700; }
        .card .card-date { color: #333; font-size: 1.1rem; font-weight: 600; }
        .badge { display: inline-block; background: #ff8800; color: #fff; border-radius: 12px; padding: 2px 10px; font-size: 0.9rem; margin-left: 8px; }
        .progress-bar-bg { background: #ffe0b3; border-radius: 8px; width: 100%; height: 10px; margin-top: 10px; }
        .progress-bar { background: #ff8800; height: 10px; border-radius: 8px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
        .dashboard-grid .card { min-width: 0; }
        .dashboard-bottom { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .announcement, .quick-links, .handbook { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px; }
        .announcement button, .handbook button { background: #ff8800; color: #fff; border: none; border-radius: 24px; padding: 10px 22px; font-size: 1rem; cursor: pointer; margin-top: 12px; font-weight: 600; box-shadow: 0 2px 8px rgba(255,136,0,0.08); transition: background 0.2s; }
        .announcement button:hover, .handbook button:hover { background: #e67600; }
        .quick-links a { color: #ff8800; text-decoration: underline; display: inline-block; margin-right: 12px; margin-bottom: 6px; font-weight: 500; }
        @media (max-width: 1100px) {
            .dashboard-grid, .dashboard-bottom { grid-template-columns: 1fr; }
            .dashboard-cards { flex-direction: column; }
            .main { padding: 16px; }
        }
        @media (max-width: 700px) {
            .sidebar, .topnav { display: none; }
            .main { margin-left: 0; padding: 8px; }
        }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php" class="active"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="manage_users.php"><span class="icon" data-feather="users"></span>Manage all users</a></li>
                <li><a href="global_settings.php"><span class="icon" data-feather="settings"></span>Set global settings</a></li>
                <li><a href="manage_roles.php"><span class="icon" data-feather="shield"></span>Manage roles & permissions</a></li>
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
        <div class="profile" style="margin-left:0;">
            <span style="font-weight:600; color:#333;">Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=ff8800&color=fff" alt="avatar" class="avatar">
            <div class="dropdown">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
    <div class="main">
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-title"><span data-feather="users"></span>Number of staff <span class="badge"><?php echo $staff_count; ?></span></div>
                <div class="progress-bar-bg"><div class="progress-bar" style="width:80%"></div></div>
            </div>
            <div class="card">
                <div class="card-title"><span data-feather="coffee"></span>Number on leave <span class="badge">16</span></div>
                <div class="progress-bar-bg"><div class="progress-bar" style="width:20%"></div></div>
            </div>
            <div class="card">
                <div class="card-title"><span data-feather="edit"></span>Profile update request <span class="badge">21</span></div>
                <div class="progress-bar-bg"><div class="progress-bar" style="width:30%"></div></div>
            </div>
            <div class="card">
                <div class="card-title"><span data-feather="calendar"></span>Next pay date</div>
                <div class="card-date">25th Jun</div>
            </div>
        </div>
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-title"><span data-feather="smile"></span>Employee happiness</div>
                <img src="https://quickchart.io/chart?c={type:'bar',data:{labels:['A','B','C','D','E'],datasets:[{label:'Happiness',data:[70,55,40,25,10],backgroundColor:'#ff8800'}]},options:{scales:{yAxes:[{ticks:{beginAtZero:true}}]}}}" alt="Happiness Chart" style="width:100%;max-width:220px;">
                <div style="margin-top:10px;"><a href="#" style="color:#ff8800;">See all surveys</a></div>
            </div>
            <div class="card">
                <div class="card-title"><span data-feather="user"></span>Employee gender</div>
                <div style="display:flex;align-items:center;gap:24px;">
                    <div style="text-align:center;">
                        <div style="font-size:2rem;color:#ff8800;">37.5%</div>
                        <div>Women</div>
                        <div style="font-size:1.2rem;">48</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:2rem;color:#ff8800;">62.5%</div>
                        <div>Men</div>
                        <div style="font-size:1.2rem;">80</div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-title"><span data-feather="inbox"></span>Requests</div>
                <div>Profile update request: <b>21</b></div>
                <div>Leave request: <b>13</b></div>
                <div>Loan request: <b>8</b></div>
                <div>Other requests: <b>10</b></div>
            </div>
            <div class="card">
                <div class="card-title"><span data-feather="trending-down"></span>Employee Turnover</div>
                <div>Q2 2019 <span style="color:#15c26b;font-weight:bold;">15%</span></div>
            </div>
            <div class="card">
                <div class="card-title"><span data-feather="trending-up"></span>Employee Retention</div>
                <div style="font-size:1.5rem;color:#15c26b;font-weight:bold;">85%</div>
            </div>
        </div>
        <div class="dashboard-bottom">
            <div class="announcement">
                <div style="font-weight:bold; margin-bottom:8px;">Create Announcement</div>
                <div>Make an announcement to your staff, e.g. new policy, event, or update.</div>
                <button id="openAnnouncementModal">Create announcement</button>
            </div>
            <div class="quick-links">
                <div style="font-weight:bold; margin-bottom:8px;">Quick links</div>
                <a href="#">Add employee</a>
                <a href="#">Profile request update</a>
                <a href="#">Payroll management</a>
                <a href="#">Marketplace</a>
                <a href="#">Special days</a>
                <a href="#">Audit trail</a>
            </div>
            <div class="handbook">
                <div style="font-weight:bold; margin-bottom:8px;">Employee handbook</div>
                <div>Access the company handbook and important documents.</div>
                <button>Add content</button>
            </div>
        </div>
    </div>
    <!-- Announcement Modal -->
    <div id="announcementModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; padding:32px; max-width:400px; width:90%; box-shadow:0 4px 32px rgba(0,0,0,0.15); position:relative;">
            <h3 style="margin-top:0; color:#ff8800;">New Announcement</h3>
            <form id="announcementForm">
                <textarea name="message" rows="4" style="width:100%; border-radius:8px; border:1.5px solid #eee; padding:12px; font-size:1rem; margin-bottom:16px;" placeholder="Type your announcement..." required></textarea>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" id="closeAnnouncementModal" style="background:#eee; color:#333; border:none; border-radius:8px; padding:8px 16px;">Cancel</button>
                    <button type="submit" style="background:#ff8800; color:#fff; border:none; border-radius:8px; padding:8px 16px;">Send</button>
                </div>
            </form>
            <div id="announcementSuccess" style="color:green; margin-top:10px; display:none;">Announcement sent!</div>
        </div>
    </div>
    <script>
        feather.replace();
        // Modal logic
        const modal = document.getElementById('announcementModal');
        document.getElementById('openAnnouncementModal').onclick = () => { modal.style.display = 'flex'; };
        document.getElementById('closeAnnouncementModal').onclick = () => { modal.style.display = 'none'; };
        // AJAX submit
        document.getElementById('announcementForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('save_announcement.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById('announcementSuccess').style.display = 'block';
                setTimeout(() => { modal.style.display = 'none'; document.getElementById('announcementSuccess').style.display = 'none'; }, 1200);
                this.reset();
            });
        };
    </script>
</body>
</html> 