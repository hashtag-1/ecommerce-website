<?php
// Seed2Greens - Admin Account Settings
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Account Settings - Seed2Greens Admin';

$error = '';
$success = '';

$admin_id = $_SESSION['admin_id'];

// Fetch current admin record
$stmt = $db->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

$current_username = $admin['username'] ?? '';

// Handle Credential Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_credentials'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_username = trim($_POST['username'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password)) {
            $error = 'Please enter your current password.';
        } elseif (!$admin || !verifyPassword($current_password, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $username_changed = ($new_username !== $admin['username']);
            $password_changed = !empty($new_password);

            if (empty($new_username)) {
                $error = 'Username cannot be empty.';
            } elseif (strlen($new_username) < 3 || strlen($new_username) > 50) {
                $error = 'Username must be between 3 and 50 characters.';
            } elseif ($username_changed) {
                $check = $db->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
                $check->execute([$new_username, $admin_id]);
                if ($check->fetch()) {
                    $error = 'That username is already taken.';
                }
            }

            if (!$error && $password_changed) {
                $password_check = validatePasswordStrength($new_password);
                if ($password_check !== true) {
                    $error = $password_check;
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New password confirmation does not match.';
                }
            }

            if (!$error) {
                $update_username = $username_changed ? $new_username : $admin['username'];
                $update_password = $password_changed ? hashPassword($new_password) : $admin['password'];

                $update = $db->prepare("UPDATE admin SET username = ?, password = ? WHERE id = ?");
                if ($update->execute([$update_username, $update_password, $admin_id])) {
                    $_SESSION['admin_username'] = $update_username;
                    setFlashMessage('Account credentials updated successfully.', 'success');
                    redirect('settings.php');
                } else {
                    $error = 'Failed to update credentials. Please try again.';
                }
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>
        
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <h2><i class="fas fa-leaf"></i> Seed2Greens</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="admin-mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
                    <h2>Account Settings</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span>Welcome, <?php echo sanitize($_SESSION['admin_name']); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </header>
            
            <main class="admin-content">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="flash-message flash-<?php echo sanitize($_SESSION['flash_type'] ?? 'success'); ?>" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo sanitize($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="flash-message flash-error" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo sanitize($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Change Username &amp; Password</h3>
                    <p style="color: var(--text-light); margin-bottom: 20px;">Enter your current password, then update your username, password, or both.</p>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="admin-form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="username">New Username</label>
                            <input type="text" id="username" name="username" value="<?php echo sanitize($current_username); ?>" placeholder="Enter new username" required>
                        </div>
                        <div class="admin-grid-2">
                            <div class="admin-form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current">
                            </div>
                            <div class="admin-form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                        </div>
                        <button type="submit" name="update_credentials" class="btn btn-primary"><i class="fas fa-save"></i> Update Credentials</button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('mobileToggle');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggle && sidebar && overlay) {
                toggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
