<?php
session_start();
require_once "config.php";

$email = $password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
        // ✅ Select all needed columns including assigned_pond
        $stmt = $conn->prepare("SELECT user_id, full_name, password, role, assigned_pond 
                                FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // ✅ Bind all 5 columns
            $stmt->bind_result($user_id, $full_name, $db_password, $role, $assigned_pond);
            $stmt->fetch();

            // ✅ Password check
          if ($password === $db_password) {
   // After password check
$_SESSION['user_id'] = $user_id;
$_SESSION['full_name'] = $full_name;
$_SESSION['email'] = $email;
$_SESSION['role'] = $role;
$_SESSION['assigned_pond'] = $assigned_pond ?? 'N/A'; // <--- important
$_SESSION['last_login'] = date("Y-m-d H:i"); // simulation

// Optional: update last_login in DB
$stmt_update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
$stmt_update->bind_param("i", $user_id);
$stmt_update->execute();
$stmt_update->close();
    // Redirect based on role
    switch ($role) {
        case 'admin': header("Location: admin_dashboard.php"); exit();
        case 'manager': header("Location: manager_dashboard.php"); exit();
        case 'staff': header("Location: staff_dashboard.php"); exit();
    }
}
            } else {
                $error = "Incorrect password.";
            }
        
            $error = "No account found with that email.";
        }

        $stmt->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Organic Tilapia System</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
    /* Global Styles */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    /* Login Card */
    .login-card {
        background: #ffffff;
        padding: 50px 40px;
        border-radius: 15px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.25);
        width: 100%;
        max-width: 400px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .login-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    }

    /* Logo / Title */
    .login-card h2 {
        color: #333;
        font-weight: 700;
        margin-bottom: 30px;
        font-size: 24px;
    }

    /* Input Fields */
    input[type="email"], input[type="password"] {
        width: 100%;
        padding: 15px 18px;
        margin: 12px 0;
        border-radius: 10px;
        border: 1px solid #ccc;
        font-size: 15px;
        transition: all 0.3s ease;
    }
    input[type="email"]:focus, input[type="password"]:focus {
        border-color: #1e90ff;
        box-shadow: 0 0 8px rgba(30,144,255,0.3);
        outline: none;
    }

    /* Login Button */
    button {
        width: 100%;
        padding: 15px;
        margin-top: 20px;
        background-color: #1e90ff;
        color: white;
        font-size: 16px;
        font-weight: 600;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    button:hover {
        background-color: #104e8b;
    }

    /* Error Message */
    .error {
        color: #e74c3c;
        font-weight: 500;
        margin-bottom: 15px;
        font-size: 14px;
    }

    /* Responsive */
    @media(max-width: 450px) {
        .login-card {
            padding: 40px 25px;
        }
        .login-card h2 {
            font-size: 22px;
        }
    }
</style>
</head>
<body>
<div class="login-card">
    <h2>Organic Tilapia System</h2>
    <?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <form action="" method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>