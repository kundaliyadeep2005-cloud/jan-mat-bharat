<?php
/**
 * 🔒 admin_auth.php
 * Handles admin session management, authentication checks, 
 * and critical security headers to prevent unauthorized access via browser history.
 */

// Use the specific session name for admin panel
if (session_status() === PHP_SESSION_NONE) {
    session_name('JAN_MAT_ADMIN_SESSION');
    session_start();
}

// 🛡️ SECURITY HEADERS: Prevent browser cache
// This ensures that when an admin logs out and presses the 'Back' button, 
// the browser is forced to re-verify the session with the server instead of showing a cached view.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// 🔐 AUTHENTICATION CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login if user is not an admin or not logged in
    header("Location: login.php");
    exit;
}
?>
