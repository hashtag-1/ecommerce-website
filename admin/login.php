<?php
// Seed2Greens - Admin Login
$page_title = 'Admin Login - Seed2Greens';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        if (loginAdmin($username, $password)) {
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-login-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .admin-login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-login-header i {
            font-size: 48px;
            color: #2e7d32;
            margin-bottom: 15px;
        }
        .admin-login-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .admin-login-header p {
            color: #777;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
            color: white;
        }
        .back-link a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div>
        <div class="admin-login-box">
            <div class="admin-login-header">
                <i class="fas fa-leaf"></i>
                <h1>Seed2Greens Admin</h1>
                <p>Login to manage the platform</p>
            </div>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                
                <button type="submit" name="admin_login" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="../index.php">&larr; Back to Website</a>
        </div>
    </div>
</body>
</html>
