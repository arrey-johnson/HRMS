<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR MANAGER') {
    header('Location: index.php');
    exit();
}
require 'db.php';

// Create jobs table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    deadline DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Open',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    if ($title && $department && $description && $requirements && $location && $deadline) {
        $stmt = $conn->prepare("INSERT INTO jobs (title, department, description, requirements, location, deadline, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssi', $title, $department, $description, $requirements, $location, $deadline, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = 'Job posted successfully!';
        } else {
            $error = 'Error posting job. Please try again.';
        }
        $stmt->close();
    } else {
        $error = 'Please fill in all fields.';
    }
}

// Fetch jobs (latest 10)
$jobs = [];
$res = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 10");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $jobs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post Job Openings - HRMS</title>
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
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px; max-width: 900px; margin: 0 auto 32px auto; }
        .form-row { display: flex; gap: 18px; margin-bottom: 16px; }
        .form-row input, .form-row select, .form-row textarea { flex: 1; }
        input, select, textarea { width: 100%; border-radius: 10px; border: 1.5px solid #eee; padding: 12px; font-size: 1rem; margin-bottom: 8px; background: #f6f8fb; transition: border 0.2s; font-family: inherit; }
        input:focus, select:focus, textarea:focus { border: 1.5px solid #ff8800; outline: none; }
        textarea { min-height: 60px; resize: vertical; }
        .submit-btn { background: #ff8800; color: #fff; border: none; border-radius: 20px; padding: 12px 32px; font-size: 1.1rem; font-weight: 700; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .submit-btn:hover { background: #e67600; }
        .jobs-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .jobs-table th, .jobs-table td { padding: 12px 10px; text-align: left; }
        .jobs-table th { background: #fff2e0; color: #ff8800; font-weight: 700; border-bottom: 2px solid #ffe0b3; }
        .jobs-table td { border-bottom: 1px solid #f0f0f0; font-size: 1rem; }
        .badge-open { background: #e6f9ed; color: #15c26b; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .badge-closed { background: #ffeaea; color: #ff3333; border-radius: 12px; padding: 2px 12px; font-size: 0.98rem; font-weight: 600; }
        .msg-success { color: #15c26b; background: #e6f9ed; border-radius: 8px; padding: 10px 18px; margin-bottom: 16px; font-weight: 600; }
        .msg-error { color: #ff3333; background: #ffeaea; border-radius: 8px; padding: 10px 18px; margin-bottom: 16px; font-weight: 600; }
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
                <li><a href="post_jobs.php" class="active"><span class="icon" data-feather="briefcase"></span>Post job openings</a></li>
                <li><a href="manage_leave.php"><span class="icon" data-feather="calendar"></span>Approve/reject leave</a></li>
                <li><a href="monitor_performance.php"><span class="icon" data-feather="bar-chart-2"></span>Monitor performance</a></li>
                <li><a href="intern_onboarding.php"><span class="icon" data-feather="user-check"></span>Manage intern onboarding</a></li>
                <li><a href="assign_interns.php"><span class="icon" data-feather="user-plus"></span>Assign interns</a></li>
            </ul>
        </nav>
        <a class="logout" href="logout.php"><span class="icon" data-feather="log-out"></span> Log out</a>
    </div>
    <div class="main">
        <h2>Post Job Openings</h2>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Add New Job Opening</h3>
            <?php if ($success): ?><div class="msg-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="msg-error"><?php echo $error; ?></div><?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="form-row">
                    <input type="text" name="title" placeholder="Job Title" required>
                    <input type="text" name="department" placeholder="Department" required>
                </div>
                <div class="form-row">
                    <input type="text" name="location" placeholder="Location" required>
                    <input type="date" name="deadline" placeholder="Deadline" required>
                </div>
                <div class="form-row">
                    <textarea name="description" placeholder="Job Description" required></textarea>
                </div>
                <div class="form-row">
                    <textarea name="requirements" placeholder="Requirements" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Post Job</button>
            </form>
        </div>
        <div class="card">
            <h3 style="color:#ff8800; margin-top:0;">Recently Posted Jobs</h3>
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                        <td><?php echo htmlspecialchars($job['department']); ?></td>
                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                        <td><?php echo htmlspecialchars($job['deadline']); ?></td>
                        <td>
                            <?php if ($job['status'] === 'Open'): ?>
                                <span class="badge-open">Open</span>
                            <?php else: ?>
                                <span class="badge-closed">Closed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>feather.replace();</script>
</body>
</html> 