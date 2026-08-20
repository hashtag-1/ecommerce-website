<?php
// Seed2Greens - Contact Page
$page_title = 'Contact Us - Seed2Greens';
require_once __DIR__ . '/includes/functions.php';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        $success = 'Thank you for your message! We will get back to you soon.';
    }
}
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Contact Us</h1>
        <p class="section-subtitle">We'd love to hear from you</p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; max-width: 900px; margin: 0 auto;">
            <div>
                <div class="admin-card" style="padding: 30px; margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; color: var(--primary);">Get in Touch</h3>
                    <p style="margin-bottom: 20px; color: var(--text-medium);">Have questions about our products or services? Feel free to reach out to us.</p>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; background: var(--bg-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <strong>Address</strong>
                            <p style="margin: 0; color: var(--text-light);">Kathmandu, Nepal</p>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; background: var(--bg-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <strong>Phone</strong>
                            <p style="margin: 0; color: var(--text-light);">+977-01-1234567</p>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; background: var(--bg-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <strong>Email</strong>
                            <p style="margin: 0; color: var(--text-light);">info@seed2greens.com</p>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <div style="width: 40px; height: 40px; background: var(--bg-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>Business Hours</strong>
                            <p style="margin: 0; color: var(--text-light);">Sun - Fri: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-card" style="padding: 30px;">
                <h3 style="margin-bottom: 20px; color: var(--primary);">Send us a Message</h3>
                
                <?php if ($error): ?>
                    <div class="flash-message flash-error" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo sanitize($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="flash-message flash-success" style="border-radius: 8px; margin-bottom: 20px; padding: 12px;">
                        <?php echo sanitize($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Your Email</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?php echo isset($_POST['subject']) ? sanitize($_POST['subject']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? sanitize($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" name="send_message" class="btn btn-primary" style="width: 100%;">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
