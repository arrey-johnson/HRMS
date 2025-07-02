<?php
session_start();
require 'db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];
    $stmt = $conn->prepare('SELECT id, name, role, password FROM users WHERE email = ? OR username = ?');
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $role, $hashed);
        $stmt->fetch();
        if (password_verify($password, $hashed)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = $role;
            if ($role === 'ADMIN') {
                header('Location: admin_dashboard.php');
            } elseif ($role === 'HR MANAGER') {
                header('Location: hr_dashboard.php');
            } elseif ($role === 'EMPLOYEE') {
                header('Location: employee_dashboard.php');
            } elseif ($role === 'INTERN') {
                header('Location: intern_dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $message = 'Invalid credentials!';
        }
    } else {
        $message = 'Invalid credentials!';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign In - HRMS</title>
    <style>
        body { background: #fff; font-family: Arial, sans-serif; margin: 0; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 32px 32px 24px 32px; box-shadow: 0 8px 32px rgba(255,136,0,0.08); color: #333; border: 1px solid #ff8800; }
        h2 { margin-top: 0; font-size: 2rem; color: #ff8800; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 12px; margin: 10px 0; border: 1.5px solid #eee; border-radius: 8px; background: #fff; color: #333; font-size: 1rem; transition: border 0.2s;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            border: 1.5px solid #ff8800;
            outline: none;
        }
        button[type="submit"] {
            width: 100%; background: #ff8800; color: #fff; border: none; border-radius: 8px; padding: 14px; font-size: 1.1rem; margin-top: 10px; cursor: pointer; transition: background 0.2s;
        }
        button[type="submit"]:hover { background: #e67600; }
        .or { text-align: center; margin: 18px 0 10px 0; color: #aaa; }
        .social-btns { display: flex; gap: 12px; }
        .social-btn { flex: 1; display: flex; align-items: center; justify-content: center; background: #423a5a; color: #fff; border: none; border-radius: 8px; padding: 10px; font-size: 1rem; cursor: pointer; }
        .social-btn img { height: 20px; margin-right: 8px; }
        .signup-link { color: #ff8800; text-decoration: underline; }
        .message { color: #ff3333; margin: 10px 0; }
        .password-wrapper { position: relative; }
        .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #ff8800; cursor: pointer; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign In</h2>
        <form method="post" autocomplete="off">
            <input type="text" name="identifier" placeholder="Email or Username" required style="font-size:1.1rem; padding:16px; margin:10px 0; border-radius:8px; border:1.5px solid #eee; width:100%; background:#fff; color:#333;">
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password', this)">&#128065;</button>
            </div>
            <button type="submit">Sign In</button>
        </form>
        <div class="message"><?php echo $message; ?></div>
        <div style="margin-bottom: 10px; font-size: 1rem;">Don't have an account? <a class="signup-link" href="signup.php">Sign Up</a></div>
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