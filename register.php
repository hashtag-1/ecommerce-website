<?php
// Seed2Greens - Registration Page
$page_title = 'Register - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = sanitize($_POST['address']);
    
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!validatePhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number';
    } else {
        $password_check = validatePasswordStrength($password);
        if ($password_check !== true) {
            $error = $password_check;
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Check if email already exists
            global $db;
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Registration failed. Please try again.';
            } else {
            if (registerUser($name, $email, $phone, $password, $address)) {
                setFlashMessage('Registration successful! Please login.', 'success');
                redirect('login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
}
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <i class="fas fa-user-plus"></i>
            <h2>Create Account</h2>
            <p>Join Seed2Greens today</p>
        </div>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error" style="border-radius: var(--radius); margin-bottom: 20px; padding: 12px;">
                <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>" placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? sanitize($_POST['phone']) : ''; ?>" placeholder="Enter 10-digit phone number" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" placeholder="Enter your address"><?php echo isset($_POST['address']) ? sanitize($_POST['address']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="At least 8 characters with uppercase, lowercase, and number" required>
                    <button type="button" class="password-toggle" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-light);">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <div style="position: relative;">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    <button type="button" class="password-toggle" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-light);">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" name="register" class="btn btn-primary" style="width: 100%;">Create Account</button>
        </form>
        
        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
