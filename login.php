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
<title></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
    /* Global Styles */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
    font-family: 'Inter', sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #142138, #0d1729);
    position: relative;
    overflow: hidden;
}

/* Background Logo / Watermark */
body::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300px;
    height: 300px;
    background: url('logo.png') no-repeat center/contain;
    opacity: 0.05;
    transform: translate(-50%, -50%);
    z-index: 0;
}

/* Animated bubbles */
@keyframes bubble {
    0% { transform: translateY(100vh) scale(0.5); opacity: 0.2; }
    50% { opacity: 0.5; }
    100% { transform: translateY(-100vh) scale(1); opacity: 0; }
}
.bubble {
    position: absolute;
    bottom: -50px;
    width: 20px;
    height: 20px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    animation: bubble 15s infinite;
}

/* Login Card */
.login-card {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 50px 40px;
    width: 100%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.login-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.5);
}

/* Title */
.login-card h2 {
    color: #ffffff;
    font-weight: 700;
    margin-bottom: 30px;
    font-size: 26px;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.3);
}

/* Input Fields */
input[type="email"], input[type="password"] {
    width: 100%;
    padding: 15px 18px;
    margin: 12px 0;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.3);
    font-size: 15px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    transition: all 0.3s ease;
}
input::placeholder {
    color: rgba(255,255,255,0.7);
}
input:focus {
    border-color: #00f2fe;
    box-shadow: 0 0 10px rgba(0,242,254,0.5);
    outline: none;
}

/* Button */
button {
    width: 100%;
    padding: 15px;
    margin-top: 20px;
    background: linear-gradient(135deg, #00f2fe, #4facfe);
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 15px rgba(0,0,0,0.3);
}
button:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 25px rgba(0,0,0,0.5);
}

/* Error Message */
.error {
    color: #ff6b6b;
    font-weight: 500;
    margin-bottom: 15px;
    font-size: 14px;
}

/* Responsive */
@media(max-width:450px){
    .login-card{padding:40px 25px;}
    .login-card h2{font-size:22px;}
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