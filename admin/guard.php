<?php
// Seed2Greens - Admin Auth Guard
// Checks admin authentication and 2FA state
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    return;
}

if (isAdmin2FAPending()) {
    redirect('2fa-verify.php');
}

redirect('login.php');
