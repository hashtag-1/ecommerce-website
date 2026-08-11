<?php
// Seed2Greens - Admin Login
$page_title = 'Admin Login - Seed2Greens';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .admin-login-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
        }
        
        .admin-login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .admin-login-header i {
            font-size: 56px;
            color: #2e7d32;
            margin-bottom: 15px;
        }
        
        .admin-login-header h1 {
            font-size: 26px;
            margin-bottom: 8px;
            color: #1b5e20;
        }
        
        .admin-login-header p {
            color: #777;
            font-size: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2e7d32;
        }
        
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #1b5e20;
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
            color: white;
        }
        
        .back-link a {
            color: white;
            text-decoration: underline;
            font-weight: 500;
        }
        
        .flash-message {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 12px;
        }
        
        .flash-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
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
                <div class="flash-message flash-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                
                <button type="submit" name="admin_login" class="btn-primary">Login</button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="../index.php">&larr; Back to Website</a>
        </div>
    </div>
</body>
</html>
