<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMPLOYEE') {
    header('Location: index.php');
    exit();
}
$employee_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Employee';
// Placeholder profile data
$profile = [
    'name' => 'David David',
    'email' => 'david@email.com',
    'phone' => '+237 699 123 456',
    'department' => 'IT',
    'role' => 'EMPLOYEE',
    'address' => '123 Main St, Douala',
    'marital_status' => 'Single',
];
$success = $error = '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>View/Update Profile - HRMS</title>
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
        .profile-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 600px; margin: 0 auto 32px auto; }
        .profile-header { display: flex; align-items: center; gap: 24px; margin-bottom: 24px; }
        .profile-avatar { width: 80px; height: 80px; border-radius: 50%; border: 2px solid #ff8800; }
        .profile-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 32px; }
        .profile-fields label { color: #888; font-size: 1rem; font-weight: 600; margin-bottom: 4px; display: block; }
        .profile-fields input, .profile-fields select, .profile-fields textarea { width: 100%; border-radius: 10px; border: 1.5px solid #eee; padding: 10px; font-size: 1rem; background: #f6f8fb; transition: border 0.2s; font-family: inherit; margin-bottom: 2px; }
        .profile-fields input:focus, .profile-fields select:focus, .profile-fields textarea:focus { border: 1.5px solid #ff8800; outline: none; }
        .profile-fields input[readonly], .profile-fields select[readonly], .profile-fields textarea[readonly] { background: #f6f8fb; color: #888; border: 1.5px solid #eee; }
        .edit-btn, .save-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 10px 28px; font-size: 1.1rem; font-weight: 700; cursor: pointer; margin-top: 18px; transition: background 0.2s; }
        .edit-btn:hover, .save-btn:hover { background: #e67600; }
        .msg-success { color: #15c26b; background: #e6f9ed; border-radius: 8px; padding: 10px 18px; margin-bottom: 16px; font-weight: 600; }
        .msg-error { color: #ff3333; background: #ffeaea; border-radius: 8px; padding: 10px 18px; margin-bottom: 16px; font-weight: 600; }
        @media (max-width: 700px) { .profile-fields { grid-template-columns: 1fr; } .profile-card { padding: 18px; } }
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
                <li><a href="payslips.php"><span class="icon" data-feather="file-text"></span>View Payslips</a></li>
                <li><a href="view_profile.php" class="active"><span class="icon" data-feather="user"></span>View Profile</a></li>
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
        <h2>View/Update Profile</h2>
        <div class="profile-card">
            <div class="profile-header">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($profile['name']); ?>&background=ff8800&color=fff" class="profile-avatar" alt="avatar">
                <div style="font-size:1.3rem; font-weight:700; color:#222;"><?php echo htmlspecialchars($profile['name']); ?></div>
            </div>
            <?php if ($success): ?><div class="msg-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="msg-error"><?php echo $error; ?></div><?php endif; ?>
            <form id="profileForm" autocomplete="off">
                <div class="profile-fields">
                    <div>
                        <label>Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" readonly>
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                    </div>
                    <div>
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($profile['phone']); ?>" readonly>
                    </div>
                    <div>
                        <label>Department</label>
                        <input type="text" name="department" value="<?php echo htmlspecialchars($profile['department']); ?>" readonly>
                    </div>
                    <div>
                        <label>Role</label>
                        <input type="text" name="role" value="<?php echo htmlspecialchars($profile['role']); ?>" readonly>
                    </div>
                    <div>
                        <label>Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($profile['address']); ?>" readonly>
                    </div>
                    <div>
                        <label>Marital Status</label>
                        <select name="marital_status" disabled>
                            <option value="Single" <?php if($profile['marital_status']==='Single') echo 'selected'; ?>>Single</option>
                            <option value="Married" <?php if($profile['marital_status']==='Married') echo 'selected'; ?>>Married</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="edit-btn" id="editBtn">Edit</button>
                <button type="submit" class="save-btn" id="saveBtn" style="display:none;">Save</button>
            </form>
        </div>
    </div>
    <script>
        feather.replace();
        // Edit/Save logic
        const form = document.getElementById('profileForm');
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        editBtn.onclick = function() {
            form.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.name !== 'role') {
                    el.removeAttribute('readonly');
                    el.removeAttribute('disabled');
                }
            });
            editBtn.style.display = 'none';
            saveBtn.style.display = '';
        };
        form.onsubmit = function(e) {
            e.preventDefault();
            form.querySelectorAll('input, select, textarea').forEach(el => {
                el.setAttribute('readonly', 'readonly');
                if (el.tagName === 'SELECT') el.setAttribute('disabled', 'disabled');
            });
            editBtn.style.display = '';
            saveBtn.style.display = 'none';
            alert('Profile updated! (Demo only)');
        };
    </script>
</body>
</html> 