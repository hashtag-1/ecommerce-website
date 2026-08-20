<?php
// Seed2Greens - Admin 2FA Setup
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/2fa.php';

if (isAdminLoggedIn()) {
    $admin = getAdminById($_SESSION['admin_id']);
    if (!$admin || !empty($admin['totp_enabled'])) {
        redirect('dashboard.php');
    }
} elseif (isAdmin2FAPending()) {
    $admin = getAdminById(getAdmin2FAUserId());
    if (!$admin) {
        clearAdmin2FASession();
        redirect('login.php');
    }
} else {
    redirect('login.php');
}

$admin_id = $admin['id'];

// Load pending secret from database (persists across devices/browsers)
$pendingSecret = $admin['totp_pending_secret'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup_2fa'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $code = preg_replace('/\s/', '', $_POST['totp_code'] ?? '');
        $secret = $pendingSecret;

        if (empty($secret)) {
            $error = 'Session expired. Please try again.';
        } elseif (empty($code) || !preg_match('/^\d{6}$/', $code)) {
            $error = 'Please enter a valid 6-digit code.';
        } elseif (!TwoFactorAuth::verifyTOTP($secret, $code)) {
            $error = 'Invalid code. Please enter the 6-digit code from your Authenticator app.';
        } else {
            $backupCodes = TwoFactorAuth::generateBackupCodes(10);
            $hashedBackupCodes = array_map([TwoFactorAuth::class, 'hashBackupCode'], $backupCodes);

            $stmt = $db->prepare("UPDATE admin SET totp_secret = ?, totp_enabled = 1, backup_codes = ?, totp_pending_secret = NULL WHERE id = ?");
            $stmt->execute([$secret, json_encode(array_values($hashedBackupCodes)), $admin_id]);

            completeAdminLogin($admin);
            setFlashMessage('Two-factor authentication has been enabled successfully.', 'success');
            redirect('settings.php');
        }
    }
}

$error = '';

// If no pending secret in DB, generate and store one
if (empty($pendingSecret)) {
    $pendingSecret = TwoFactorAuth::generateSecret();
    $stmt = $db->prepare("UPDATE admin SET totp_pending_secret = ? WHERE id = ?");
    $stmt->execute([$pendingSecret, $admin_id]);
}

if (empty($_SESSION['admin_2fa_backup_codes'])) {
    $_SESSION['admin_2fa_backup_codes'] = TwoFactorAuth::generateBackupCodes(10);
}

$secret = $pendingSecret;
$qrCodeUrl = TwoFactorAuth::getQrCodeUrl($admin['username'], $secret);
$provisioningUri = TwoFactorAuth::getProvisioningUri($admin['username'], $secret);
$backupCodes = $_SESSION['admin_2fa_backup_codes'] ?? [];

$page_title = 'Enable Two-Factor Authentication - Seed2Greens Admin';
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
            max-width: 520px;
        }
        .admin-login-header { text-align: center; margin-bottom: 35px; }
        .admin-login-header i { font-size: 56px; color: #2e7d32; margin-bottom: 15px; }
        .admin-login-header h1 { font-size: 26px; margin-bottom: 8px; color: #1b5e20; }
        .admin-login-header p { color: #777; font-size: 15px; }
        .qr-section { text-align: center; margin-bottom: 25px; }
        .qr-section img { border: 1px solid #e0e0e0; border-radius: 8px; padding: 10px; max-width: 180px; }
        .secret-display {
            background: #f5f5f5;
            border: 1px dashed #ccc;
            border-radius: 8px;
            padding: 12px;
            margin: 15px 0;
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 2px;
            word-break: break-all;
            text-align: center;
        }
        .backup-section {
            background: #fff8e1;
            border: 1px solid #ffe0b2;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .backup-section h4 { margin-bottom: 10px; color: #e65100; }
        .backup-codes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-family: monospace;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .backup-code {
            background: #fff;
            border: 1px solid #ffe0b2;
            border-radius: 4px;
            padding: 6px 10px;
            text-align: center;
        }
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
        .flash-success { background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
        .instructions {
            background: #e3f2fd;
            color: #0d47a1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.6;
        }
        .instructions ol { margin-left: 20px; }
        .instructions ol li { margin-bottom: 5px; }
        .backup-warning {
            background: #ffebee;
            color: #c62828;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 10px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div>
        <div class="admin-login-box">
            <div class="admin-login-header">
                <i class="fas fa-mobile-alt"></i>
                <h1>Enable Two-Factor Authentication</h1>
                <p>Secure your admin account with Google Authenticator</p>
            </div>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error">
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="instructions">
                <strong>Setup Instructions:</strong>
                <ol>
                    <li>Install Google Authenticator on your phone</li>
                    <li>Scan the QR code or enter the secret key manually</li>
                    <li>Save your backup codes in a secure location</li>
                    <li>Enter the 6-digit code from the app to verify</li>
                </ol>
            </div>
            
            <div class="qr-section">
                <?php if ($qrCodeUrl): ?>
                    <img src="<?php echo sanitize($qrCodeUrl); ?>" alt="QR Code for Google Authenticator" width="180" height="180">
                    <p style="margin-top: 10px; font-size: 13px; color: #777;">
                        <a href="<?php echo sanitize($provisioningUri); ?>" target="_blank" style="color: #2e7d32;">Can't scan? Open in Authenticator app</a>
                    </p>
                <?php else: ?>
                    <p style="color: #c62828;">Unable to generate QR code. Please use the manual entry key below.</p>
                <?php endif; ?>
                
                <div class="secret-display">
                    <?php echo sanitize($secret); ?>
                </div>
                <p style="font-size: 12px; color: #777;">Manual entry key</p>
            </div>
            
            <?php if (!empty($backupCodes)): ?>
                <div class="backup-section">
                    <h4><i class="fas fa-key"></i> Your Backup Codes</h4>
                    <div class="backup-codes">
                        <?php foreach ($backupCodes as $code): ?>
                            <div class="backup-code"><?php echo sanitize($code); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="backup-warning">
                        <i class="fas fa-exclamation-triangle"></i> Save these codes now. Each code can only be used once. You will not be able to see them again.
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="totp_code">Verification Code</label>
                    <input type="text" id="totp_code" name="totp_code" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required autofocus autocomplete="off">
                </div>
                <button type="submit" name="setup_2fa" class="btn-primary">Enable 2FA</button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="settings.php">&larr; Cancel</a>
        </div>
    </div>
</body>
</html>
