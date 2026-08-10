<?php
// Seed2Greens - User Profile Page
$page_title = 'My Profile - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    setFlashMessage('Please login to view your profile', 'error');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get current user data
global $db;
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    
    if (empty($name) || empty($phone)) {
        $error = 'Name and phone are required';
    } elseif (!validatePhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number';
    } else {
        // Update basic info
        $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $user_id]);
        
        // Update password if provided
        if (!empty($current_password) && !empty($new_password)) {
            if (!verifyPassword($current_password, $user['password'])) {
                $error = 'Current password is incorrect';
            } elseif (strlen($new_password) < 6) {
                $error = 'New password must be at least 6 characters';
            } elseif ($new_password !== $confirm_new_password) {
                $error = 'New passwords do not match';
            } else {
                $new_hashed = hashPassword($new_password);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_hashed, $user_id]);
                $success = 'Profile and password updated successfully!';
            }
        } else {
            $success = 'Profile updated successfully!';
        }
        
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_address'] = $address;
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="section">
    <div class="container">
        <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">My Profile</h1>
        
        <div class="profile-card">
            <?php if ($error): ?>
                <div class="flash-message flash-error" style="border-radius: var(--radius); margin-bottom: 20px; padding: 12px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="flash-message flash-success" style="border-radius: var(--radius); margin-bottom: 20px; padding: 12px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Account Information</h3>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo sanitize($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" value="<?php echo sanitize($user['email']); ?>" disabled style="background: var(--bg-light);">
                    <small style="color: var(--text-light);">Email cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo sanitize($user['phone']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo sanitize($user['address']); ?></textarea>
                </div>
                
                <h3 style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary);">Change Password</h3>
                <p style="color: var(--text-light); margin-bottom: 15px; font-size: 14px;">Leave blank if you don't want to change your password</p>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_new_password">Confirm New Password</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password">
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
