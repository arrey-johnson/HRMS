<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
require 'db.php';
// Fetch all admins
$admins = [];
$res = $conn->query("SELECT id, name, username, email FROM users WHERE role = 'ADMIN'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $admins[] = $row;
    }
}
// Fetch all employees (for mentor selection)
$employees = [];
$res2 = $conn->query("SELECT id, name, username FROM users WHERE role = 'EMPLOYEE'");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $employees[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assign Interns - HRMS</title>
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
        .admins-table-container { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 900px; margin: 0 auto 32px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 12px 10px; text-align: left; }
        th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        tr { background: #fff; transition: background 0.2s; }
        tr:hover { background: #fff7ec; }
        td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .assign-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .assign-btn:hover { background: #e67600; }
        .mentor-form-modal { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.2); z-index: 1000; align-items: center; justify-content: center; }
        .mentor-form-card { background: #fff; border-radius: 16px; padding: 28px 32px; max-width: 350px; width: 95%; box-shadow: 0 4px 32px rgba(0,0,0,0.12); position: relative; display: flex; flex-direction: column; }
        .mentor-form-card h3 { margin-top: 0; color: #ff8800; font-size: 1.2rem; }
        .mentor-list { max-height: 180px; overflow-y: auto; margin-bottom: 18px; border: 1.5px solid #eee; border-radius: 8px; padding: 8px; background: #f6f8fb; }
        .mentor-list label { display: block; padding: 6px 0; cursor: pointer; }
        .close-mentor-modal { position: absolute; top: 12px; right: 18px; background: none; border: none; color: #ff8800; font-size: 1.3rem; cursor: pointer; }
        .save-changes-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 12px 32px; font-size: 1.1rem; font-weight: 700; cursor: pointer; margin: 32px auto 0 auto; display: block; transition: background 0.2s; }
        .save-changes-btn:hover { background: #e67600; }
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
                <li><a href="manage_roles.php"><span class="icon" data-feather="shield"></span>Manage roles & permissions</a></li>
                <li><a href="analytics_logs.php"><span class="icon" data-feather="bar-chart-2"></span>View analytics & logs</a></li>
                <li><a href="assign_interns.php" class="active"><span class="icon" data-feather="user-plus"></span>Assign Interns</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="topnav">
        <div class="search-area"></div>
        <div class="profile" style="margin-left:auto; display:flex; align-items:center; gap:18px;">
            <span style="font-weight:600; color:#333;">Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=ff8800&color=fff" alt="avatar" class="avatar">
        </div>
    </div>
    <div class="main">
        <h2>Assign Interns to Mentors</h2>
        <div class="admins-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['id']); ?></td>
                        <td><?php echo htmlspecialchars($admin['name']); ?></td>
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        <td><button class="assign-btn" onclick="openMentorModal(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars(addslashes($admin['name'])); ?>')">Assign</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Mentor Assignment Modal -->
        <div class="mentor-form-modal" id="mentorModal">
            <div class="mentor-form-card">
                <button class="close-mentor-modal" onclick="closeMentorModal()">&times;</button>
                <h3>Assign Mentor for <span id="adminName"></span></h3>
                <form id="mentorAssignForm">
                    <div class="mentor-list" id="mentorList">
                        <?php foreach ($employees as $emp): ?>
                        <label><input type="radio" name="mentor_id" value="<?php echo $emp['id']; ?>"> <?php echo htmlspecialchars($emp['name'] . ' (' . $emp['username'] . ')'); ?></label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="admin_id" id="adminIdInput">
                    <button type="submit" class="assign-btn" style="width:100%;margin-top:10px;">Assign Mentor</button>
                </form>
            </div>
        </div>
        <button class="save-changes-btn">Save Changes</button>
    </div>
    <script>
    feather.replace();
    // Modal logic
    function openMentorModal(adminId, adminName) {
        document.getElementById('mentorModal').style.display = 'flex';
        document.getElementById('adminName').textContent = adminName;
        document.getElementById('adminIdInput').value = adminId;
    }
    function closeMentorModal() {
        document.getElementById('mentorModal').style.display = 'none';
        document.getElementById('mentorAssignForm').reset();
    }
    // Optionally handle form submit with AJAX
    document.getElementById('mentorAssignForm').onsubmit = function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Assigning...';
        fetch('assign_mentor.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Assign Mentor';
            if (data.trim() === 'success') {
                closeMentorModal();
                alert('Mentor assigned successfully!');
            } else {
                alert('Error assigning mentor.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Assign Mentor';
            alert('Error assigning mentor.');
        });
    };
    </script>
</body>
</html> 
 