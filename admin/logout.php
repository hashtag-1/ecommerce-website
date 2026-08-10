<?php
// Seed2Greens - Admin Logout
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    logoutAdmin();
    setFlashMessage('You have been logged out from admin panel.', 'success');
}

redirect('login.php');
