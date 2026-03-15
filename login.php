<?php
// login.php
session_start();
require_once 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] == 'manager') {
        header('Location: manager_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] == 'staff') {
        header('Location: staff_dashboard.php');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? ''; // Plain text as per your DB
    
    $query = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        
        // Update last login
        $update = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param('i', $user['user_id']);
        $stmt->execute();
        
        if ($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');
            exit;
        } elseif ($user['role'] == 'manager') {
            header('Location: manager_dashboard.php');
            exit;
        } else {
            header('Location: staff_dashboard.php');
            exit;
        }
    } else {
        $error = 'Invalid email or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Organic Tilapia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #142138 0%, #0d1729 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo i {
            font-size: 3rem;
            color: #3b82f6;
        }
        
        .logo h2 {
            color: white;
            margin-top: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .login-btn:hover {
            background: #2563eb;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .demo-credentials {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .demo-credentials span {
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-fish"></i>
            <h2>Organic Tilapia</h2>
        </div>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <div class="demo-credentials">
            <p>Admin: <span>admin@company.com</span> / <span>admin123</span></p>
            <p>Manager: <span>manager@company.com</span> / <span>manager123</span></p>
            <p>Staff: <span>staff1@company.com</span> / <span>staff123</span></p>
        </div>
    </div>
</body>
</html>