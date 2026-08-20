<?php
// Seed2Greens - Login Page
$page_title = 'Login - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } elseif (!checkLoginRateLimit('user')) {
            $error = 'Too many login attempts. Please try again later.';
        } else {
            $login_result = loginUser($email, $password);
            if ($login_result === true) {
                setFlashMessage('Login successful! Welcome back.', 'success');
                redirect('index.php');
            } elseif ($login_result === 'rate_limited') {
                $error = 'Too many login attempts. Please try again later.';
            } else {
                $error = 'Invalid email or password';
            }
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <i class="fas fa-user-circle"></i>
            <h2>Welcome Back</h2>
            <p>Login to your Seed2Greens account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error" style="border-radius: var(--radius); margin-bottom: 20px; padding: 12px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-light);">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <div class="form-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
