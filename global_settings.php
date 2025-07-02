<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: index.php');
    exit();
}
require 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Global Settings - HRMS</title>
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
        .settings-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 500px; margin-bottom: 32px; }
        h2 { color: #ff8800; margin-top: 0; }
        .section-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 10px; color: #ff8800; }
        label { font-weight: 500; }
        .theme-options { display: flex; gap: 24px; margin-bottom: 18px; }
        .theme-radio { accent-color: #ff8800; }
        .notif-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .notif-sound { width: 180px; }
        .pill-btn { border: none; border-radius: 20px; padding: 8px 22px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s, color 0.2s; }
        .save-btn { background: #ff8800; color: #fff; }
        .save-btn:hover { background: #e67600; }
        .change-pw-form input { width: 100%; border-radius: 10px; border: 1.5px solid #eee; padding: 12px; font-size: 1rem; margin-bottom: 12px; background: #f6f8fb; transition: border 0.2s; }
        .change-pw-form input:focus { border: 1.5px solid #ff8800; outline: none; }
        .change-pw-form .modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
        .success-msg { color: green; margin-top: 10px; display: none; }
        .error-msg { color: #ff3333; margin-top: 10px; display: none; }
        @media (max-width: 1100px) {
            .main { padding: 16px; }
        }
        @media (max-width: 700px) {
            .sidebar, .topnav { display: none; }
            .main { margin-left: 0; padding: 8px; }
        }
        .custom-select-wrapper {
            position: relative;
            display: inline-block;
            width: 180px;
        }
        .custom-select {
            width: 100%;
            padding: 10px 36px 10px 12px;
            border-radius: 10px;
            border: 1.5px solid #eee;
            background: #f6f8fb;
            font-size: 1rem;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            transition: border 0.2s;
            color: #333;
            font-family: inherit;
        }
        .custom-select:focus {
            border: 1.5px solid #ff8800;
            outline: none;
        }
        .custom-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #ff8800;
        }
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
                <li><a href="global_settings.php" class="active"><span class="icon" data-feather="settings"></span>Set global settings</a></li>
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
        <h2>Global Settings & Configurations</h2>
        <div class="settings-card">
            <div class="section-title">Theme</div>
            <div class="theme-options">
                <label><input type="radio" name="theme" value="white" class="theme-radio" checked> White</label>
                <label><input type="radio" name="theme" value="black" class="theme-radio"> Black</label>
            </div>
            <div class="success-msg" id="themeSuccess">Theme changed!</div>
            <hr style="margin:32px 0 24px 0; border:none; border-top:1.5px solid #f0f0f0;">
            <div class="section-title">Notifications</div>
            <div class="notif-row">
                <input type="checkbox" id="allowNotif" checked>
                <label for="allowNotif">Allow notifications</label>
            </div>
            <div class="notif-row">
                <label for="notifSound">Notification sound:</label>
                <div class="custom-select-wrapper">
                    <select id="notifSound" class="notif-sound custom-select">
                        <option value="ding">Ding</option>
                        <option value="chime">Chime</option>
                        <option value="pop">Pop</option>
                    </select>
                    <span class="custom-arrow" data-feather="chevron-down"></span>
                </div>
            </div>
            <div class="success-msg" id="notifSuccess">Notification updated!</div>
            <hr style="margin:32px 0 24px 0; border:none; border-top:1.5px solid #f0f0f0;">
            <div class="section-title">Change Password</div>
            <form class="change-pw-form" id="changePwForm">
                <input type="password" name="current_pw" placeholder="Current password" required>
                <input type="password" name="new_pw" placeholder="New password" required>
                <input type="password" name="confirm_pw" placeholder="Confirm new password" required>
                <div class="modal-actions">
                    <button type="submit" class="pill-btn save-btn">Change Password</button>
                </div>
            </form>
            <div class="success-msg" id="pwSuccess">Password changed!</div>
            <div class="error-msg" id="pwError">Error changing password.</div>
        </div>
    </div>
    <script>
    feather.replace();
    // Theme change instant
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Optionally, apply a class to body for dark/light theme
            document.getElementById('themeSuccess').style.display = 'block';
            setTimeout(() => { document.getElementById('themeSuccess').style.display = 'none'; }, 1200);
            // TODO: Save theme via AJAX if persistence is needed
        });
    });
    // Notification allow instant
    const allowNotif = document.getElementById('allowNotif');
    allowNotif.addEventListener('change', function() {
        document.getElementById('notifSuccess').style.display = 'block';
        setTimeout(() => { document.getElementById('notifSuccess').style.display = 'none'; }, 1200);
        // TODO: Save allowNotif via AJAX if persistence is needed
    });
    // Notification sound instant
    const notifSound = document.getElementById('notifSound');
    notifSound.addEventListener('change', function() {
        document.getElementById('notifSuccess').style.display = 'block';
        setTimeout(() => { document.getElementById('notifSuccess').style.display = 'none'; }, 1200);
        // TODO: Save notifSound via AJAX if persistence is needed
    });
    // Change password AJAX
    document.getElementById('changePwForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                document.getElementById('pwSuccess').style.display = 'block';
                document.getElementById('pwError').style.display = 'none';
                this.reset();
                setTimeout(() => { document.getElementById('pwSuccess').style.display = 'none'; }, 1200);
            } else {
                document.getElementById('pwError').style.display = 'block';
                document.getElementById('pwSuccess').style.display = 'none';
            }
        });
    };
    </script>
</body>
</html> 