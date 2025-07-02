<?php
require 'db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $marital_status = $_POST['marital_status'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $terms = isset($_POST['terms']);
    if ($password !== $confirm) {
        $message = 'Passwords do not match!';
    } elseif (empty($first_name) || empty($last_name) || empty($username) || empty($phone) || empty($role) || empty($marital_status) || empty($email) || empty($password)) {
        $message = 'All fields are required!';
    } elseif (!$terms) {
        $message = 'You must agree to the Terms & Conditions!';
    } else {
        $name = $first_name . ' ' . $last_name;
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, username, phone, role, marital_status, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssss', $name, $username, $phone, $role, $marital_status, $email, $hashed);
        if ($stmt->execute()) {
            $message = 'Registration successful! <a href="index.php">Sign in</a>';
        } else {
            $message = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create an account - HRMS</title>
    <style>
        body { background: #fff; font-family: Arial, sans-serif; margin: 0; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 32px 32px 24px 32px; box-shadow: 0 8px 32px rgba(255,136,0,0.08); color: #333; border: 1px solid #ff8800; }
        h2 { margin-top: 0; font-size: 2rem; color: #ff8800; }
        .row { display: flex; gap: 12px; position: relative; }
        .row input, .row select, .row .password-wrapper { flex: 1; }
        input[type="text"], input[type="email"], input[type="password"], .row select {
            width: 100%; padding: 12px; margin: 10px 0; border: 1.5px solid #eee; border-radius: 8px; background: #fff; color: #333; font-size: 1rem; transition: border 0.2s;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, .row select:focus {
            border: 1.5px solid #ff8800;
            outline: none;
        }
        .row select {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23ff8800" height="16" viewBox="0 0 20 20" width="16" xmlns="http://www.w3.org/2000/svg"><path d="M5.516 7.548a.75.75 0 0 1 1.06 0L10 10.97l3.424-3.423a.75.75 0 1 1 1.06 1.06l-3.954 3.954a.75.75 0 0 1-1.06 0L5.516 8.608a.75.75 0 0 1 0-1.06z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 18px 18px;
        }
        .checkbox-label { display: flex; align-items: center; font-size: 0.95rem; margin: 10px 0; }
        .checkbox-label input { margin-right: 8px; accent-color: #ff8800; }
        .terms-link { color: #ff8800; text-decoration: underline; }
        button[type="submit"] {
            width: 100%; background: #ff8800; color: #fff; border: none; border-radius: 8px; padding: 14px; font-size: 1.1rem; margin-top: 10px; cursor: pointer; transition: background 0.2s;
        }
        button[type="submit"]:hover { background: #e67600; }
        .login-link { color: #ff8800; text-decoration: underline; }
        .message { color: #ff3333; margin: 10px 0; }
        .password-wrapper { position: relative; }
        .toggle-password { position: absolute; right: 24px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #ff8800; cursor: pointer; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <div style="margin-bottom: 10px; font-size: 1rem;">Already have an account? <a class="login-link" href="index.php">Log in</a></div>
        <form method="post" autocomplete="off">
            <div class="row">
                <input type="text" name="first_name" placeholder="First name" required>
                <input type="text" name="last_name" placeholder="Last name" required>
            </div>
            <div class="row">
                <input type="text" name="username" placeholder="Username" required>
                <input type="text" name="phone" placeholder="Phone number" required>
            </div>
            <div class="row">
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
            <div class="row">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="row">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password', this)" style="position:absolute; right:24px; top:50%; transform:translateY(-50%); background:none; border:none; color:#ff8800; cursor:pointer; font-size:1.2rem;"></button>
            </div>
            <div class="row">
                <input type="password" name="confirm" id="confirm" placeholder="Confirm password" required>
            </div>
            <div class="checkbox-label">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">I agree to the <a class="terms-link" href="#">Terms & Conditions</a></label>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <div class="message"><?php echo $message; ?></div>
    </div>
    <script>
    function togglePassword(id, btn) {
        var input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '&#128064;';
        } else {
            input.type = 'password';
            btn.innerHTML = '&#128065;';
        }
    }
    </script>
</body>
</html> 