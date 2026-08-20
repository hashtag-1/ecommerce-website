<?php
// Seed2Greens - Admin 2FA Verification
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/2fa.php';

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

if (!isAdmin2FAPending()) {
    redirect('login.php');
}

$error = '';
$admin_id = getAdmin2FAUserId();
$admin = getAdminById($admin_id);

if (!$admin || empty($admin['totp_enabled']) || empty($admin['totp_secret'])) {
    clearAdmin2FASession();
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_2fa'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (!checkTOTPRateLimit()) {
        $lockoutUntil = $_SESSION['admin_totp_lockout_until'] ?? time() + 900;
        $minutes = ceil(($lockoutUntil - time()) / 60);
        $error = "Too many failed attempts. Please try again in $minutes minutes.";
    } else {
        $code = preg_replace('/\s/', '', $_POST['totp_code'] ?? '');
        $backupCode = strtoupper(trim($_POST['backup_code'] ?? ''));

        $verified = false;

        if (!empty($code)) {
            $verified = TwoFactorAuth::verifyTOTP($admin['totp_secret'], $code);
        } elseif (!empty($backupCode)) {
            $hashedCodes = json_decode($admin['backup_codes'], true) ?: [];
            $matchIndex = TwoFactorAuth::verifyBackupCode($backupCode, $hashedCodes);
            if ($matchIndex !== false) {
                TwoFactorAuth::removeBackupCode($hashedCodes, $matchIndex);
                $stmt = $db->prepare("UPDATE admin SET backup_codes = ? WHERE id = ?");
                $stmt->execute([json_encode(array_values($hashedCodes)), $admin_id]);
                $verified = true;
            }
        }

        if ($verified) {
            completeAdminLogin($admin);
            redirect('dashboard.php');
        } else {
            recordTOTPAttempt();
            $error = 'Invalid authentication code. Please try again.';
        }
    }
}

$page_title = 'Two-Factor Authentication - Seed2Greens Admin';
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .admin-login-header { text-align: center; margin-bottom: 35px; }
        .admin-login-header i { font-size: 56px; color: #2e7d32; margin-bottom: 15px; }
        .admin-login-header h1 { font-size: 26px; margin-bottom: 8px; color: #1b5e20; }
        .admin-login-header p { color: #777; font-size: 15px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #2e7d32; }
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
        .btn-primary:hover { background: #1b5e20; }
        .back-link { text-align: center; margin-top: 25px; color: white; }
        .back-link a { color: white; text-decoration: underline; font-weight: 500; }
        .flash-message { border-radius: 8px; margin-bottom: 20px; padding: 12px; }
        .flash-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .tab-group { display: flex; margin-bottom: 20px; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .tab-group button {
            flex: 1;
            padding: 10px;
            border: none;
            background: #f5f5f5;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
        }
        .tab-group button.active { background: #2e7d32; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .backup-warning {
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #ffe0b2;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div>
        <div class="admin-login-box">
            <div class="admin-login-header">
                <i class="fas fa-shield-alt"></i>
                <h1>Two-Factor Authentication</h1>
                <p>Enter the 6-digit code from your Authenticator app</p>
            </div>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error">
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="tab-group">
                <button type="button" class="active" onclick="switchTab('totp')">Authenticator App</button>
                <button type="button" onclick="switchTab('backup')">Backup Code</button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div id="tab-totp" class="tab-content active">
                    <div class="form-group">
                        <label for="totp_code">Authentication Code</label>
                        <input type="text" id="totp_code" name="totp_code" placeholder="000000" maxlength="6" pattern="\d{6}" autocomplete="off">
                    </div>
                </div>
                
                <div id="tab-backup" class="tab-content">
                    <div class="backup-warning">
                        <i class="fas fa-exclamation-triangle"></i> Backup codes are for one-time use only.
                    </div>
                    <div class="form-group">
                        <label for="backup_code">Backup Code</label>
                        <input type="text" id="backup_code" name="backup_code" placeholder="Enter backup code" autocomplete="off">
                    </div>
                </div>
                
                <button type="submit" name="verify_2fa" class="btn-primary">Verify</button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="login.php">&larr; Back to Login</a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-group button').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
            event.target.classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');
            if (tab === 'totp') {
                document.getElementById('totp_code').focus();
            } else {
                document.getElementById('backup_code').focus();
            }
        }
    </script>
</body>
</html>
