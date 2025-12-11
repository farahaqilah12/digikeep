<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

/**
 * Ensure user is logged in
 */
function checkLogin($roleRequired = null) {
    // If session exists – user stays logged in even if DB temporarily fails
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header("Location: login.php"); 
        exit;
    }

    // Check role if needed
    if ($roleRequired && $_SESSION['role'] !== $roleRequired) {
        header("Location: no_permission.php");
        exit;
    }

    // Refresh session timestamp to prevent auto-expiry
    $_SESSION['last_active'] = time();
}

/**
 * Get current logged-in user's info
 */
function currentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'last_active' => $_SESSION['last_active'] ?? null
    ];
}
