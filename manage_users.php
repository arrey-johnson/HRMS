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
    <title>Manage All Users - HRMS</title>
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
        .topnav .profile { display: flex; align-items: center; gap: 18px; position: relative; margin-left: auto; }
        .topnav .avatar { width: 38px; height: 38px; border-radius: 50%; border: 2px solid #ff8800; }
        .topnav .dropdown { display: none; position: absolute; right: 0; top: 48px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; min-width: 160px; }
        .topnav .profile:hover .dropdown { display: block; }
        .topnav .dropdown a { display: block; padding: 12px 18px; color: #333; text-decoration: none; border-bottom: 1px solid #f0f0f0; }
        .topnav .dropdown a:last-child { border-bottom: none; }
        .topnav .dropdown a:hover { background: #ff8800; color: #fff; }
        .main { margin-left: 250px; padding: 32px; background: #f6f8fb; min-height: 100vh; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; background: #fff; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #ff8800; color: #fff; font-weight: 600; }
        tr:hover { background: #fff2e0; }
        .pill-btn { border: none; border-radius: 20px; padding: 7px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s, color 0.2s; }
        .edit-btn { background: #3498db; color: #fff; }
        .edit-btn:hover { background: #217dbb; }
        .delete-btn { background: #ff3333; color: #fff; }
        .delete-btn:hover { background: #c82333; }
        #addUserModal { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:1000; align-items:center; justify-content:center; }
        #addUserModal .modal-card { background:#fff; border-radius:20px; padding:36px 32px 28px 32px; max-width:440px; width:95%; box-shadow:0 8px 40px rgba(0,0,0,0.18); position:relative; }
        #addUserModal h3 { margin-top:0; color:#ff8800; font-size:1.3rem; font-weight:700; letter-spacing:0.5px; }
        #addUserModal input, #addUserModal select { width:100%; border-radius:10px; border:1.5px solid #eee; padding:12px; font-size:1rem; margin-bottom:12px; background:#f6f8fb; transition: border 0.2s; }
        #addUserModal input:focus, #addUserModal select:focus { border:1.5px solid #ff8800; outline:none; }
        #addUserModal .modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:8px; }
        #addUserModal .cancel-btn { background:#eee; color:#333; }
        #addUserModal .cancel-btn:hover { background:#ccc; }
        #addUserModal .add-btn { background:#ff8800; color:#fff; }
        #addUserModal .add-btn:hover { background:#e67600; }
        #addUserSuccess { color:green; margin-top:10px; display:none; }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
    <div class="sidebar">
        <div class="brand">HRMS</div>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php"><span class="icon" data-feather="home"></span>Dashboard</a></li>
                <li><a href="manage_users.php" class="active"><span class="icon" data-feather="users"></span>Manage all users</a></li>
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
        <h2>Manage All Users</h2>
        <div style="display:flex; justify-content:flex-end; margin-bottom:18px;">
            <button id="addUserBtn" style="background:#ff8800; color:#fff; border:none; border-radius:8px; padding:10px 22px; font-size:1rem; font-weight:600; cursor:pointer;">Add User</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Marital Status</th>
                <th>Date Registered</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php
            $result = $conn->query("SELECT id, name, marital_status, created_at, phone, address, email FROM users ORDER BY id DESC");
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $uid = htmlspecialchars($row['id']);
                    echo '<tr id="user-row-' . $uid . '">' .
                        '<td>' . $uid . '</td>' .
                        '<td class="name">' . htmlspecialchars($row['name']) . '</td>' .
                        '<td class="marital_status">' . htmlspecialchars($row['marital_status']) . '</td>' .
                        '<td>' . htmlspecialchars(substr($row['created_at'], 0, 10)) . '</td>' .
                        '<td class="phone">' . htmlspecialchars($row['phone']) . '</td>' .
                        '<td class="address">' . htmlspecialchars($row['address']) . '</td>' .
                        '<td class="email">' . htmlspecialchars($row['email']) . '</td>' .
                        '<td>' .
                        '<button class="pill-btn edit-btn" data-id="' . $uid . '"><span data-feather="edit-2"></span>Edit</button> ' .
                        '<button class="pill-btn delete-btn" data-id="' . $uid . '"><span data-feather="trash-2"></span>Delete</button>' .
                        '</td>' .
                        '</tr>';
                }
            } else {
                echo '<tr><td colspan="8">No users found.</td></tr>';
            }
            ?>
        </table>
        <script>
        // Edit logic
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.onclick = function() {
                const id = this.dataset.id;
                const row = document.getElementById('user-row-' + id);
                if (!row) return;
                // Get current values
                const name = row.querySelector('.name').innerText;
                const marital = row.querySelector('.marital_status').innerText;
                const phone = row.querySelector('.phone').innerText;
                const address = row.querySelector('.address').innerText;
                const email = row.querySelector('.email').innerText;
                // Replace with inputs
                row.querySelector('.name').innerHTML = `<input type='text' value='${name}' style='width:100px;'>`;
                row.querySelector('.marital_status').innerHTML = `<input type='text' value='${marital}' style='width:80px;'>`;
                row.querySelector('.phone').innerHTML = `<input type='text' value='${phone}' style='width:100px;'>`;
                row.querySelector('.address').innerHTML = `<input type='text' value='${address}' style='width:120px;'>`;
                row.querySelector('.email').innerHTML = `<input type='text' value='${email}' style='width:140px;'>`;
                // Action buttons
                row.querySelector('td:last-child').innerHTML = `<button class='save-btn' data-id='${id}'>Save</button> <button class='cancel-btn' data-id='${id}'>Cancel</button>`;
                // Save logic
                row.querySelector('.save-btn').onclick = function() {
                    const newName = row.querySelector('.name input').value;
                    const newMarital = row.querySelector('.marital_status input').value;
                    const newPhone = row.querySelector('.phone input').value;
                    const newAddress = row.querySelector('.address input').value;
                    const newEmail = row.querySelector('.email input').value;
                    fetch('update_user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${id}&name=${encodeURIComponent(newName)}&marital_status=${encodeURIComponent(newMarital)}&phone=${encodeURIComponent(newPhone)}&address=${encodeURIComponent(newAddress)}&email=${encodeURIComponent(newEmail)}`
                    }).then(res => res.text()).then(data => { location.reload(); });
                };
                // Cancel logic
                row.querySelector('.cancel-btn').onclick = function() { location.reload(); };
            };
        });
        // Delete logic
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.onclick = function() {
                const id = this.dataset.id;
                if (confirm('Are you sure you want to delete this user?')) {
                    fetch('delete_user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${id}`
                    }).then(res => res.text()).then(data => { location.reload(); });
                }
            };
        });
        </script>
        <!-- Add User Modal -->
        <div id="addUserModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:1000; align-items:center; justify-content:center;">
            <div class="modal-card">
                <h3>Add New User</h3>
                <form id="addUserForm">
                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <input type="text" name="first_name" placeholder="First name" required>
                        <input type="text" name="last_name" placeholder="Last name" required>
                    </div>
                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="text" name="phone" placeholder="Phone number" required>
                    </div>
                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <select name="role" required>
                            <option value="" disabled selected>Role</option>
                            <option value="ADMIN">ADMIN</option>
                            <option value="HR MANAGER">HR MANAGER</option>
                            <option value="EMPLOYEE">EMPLOYEE</option>
                            <option value="INTERN">INTERN</option>
                        </select>
                        <select name="marital_status" required>
                            <option value="SINGLE" selected>SINGLE</option>
                            <option value="MARRIED">MARRIED</option>
                        </select>
                    </div>
                    <input type="text" name="address" placeholder="Address" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="confirm" placeholder="Confirm password" required>
                    <div class="modal-actions">
                        <button type="button" id="closeAddUserModal" class="pill-btn cancel-btn">Cancel</button>
                        <button type="submit" class="pill-btn add-btn">Add</button>
                    </div>
                </form>
                <div id="addUserSuccess">User added!</div>
            </div>
        </div>
        <script>
        // Modal logic
        document.getElementById('addUserBtn').onclick = () => { document.getElementById('addUserModal').style.display = 'flex'; };
        document.getElementById('closeAddUserModal').onclick = () => { document.getElementById('addUserModal').style.display = 'none'; };
        document.getElementById('addUserForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('add_user.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById('addUserSuccess').style.display = 'block';
                setTimeout(() => { document.getElementById('addUserModal').style.display = 'none'; document.getElementById('addUserSuccess').style.display = 'none'; location.reload(); }, 1200);
                this.reset();
            });
        };
        </script>
    </div>
    <script>
        feather.replace();
    </script>
</body>
</html> 