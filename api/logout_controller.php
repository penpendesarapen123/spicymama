<?php
require_once __DIR__ . '/../_init.php';

// Check if a session is already started to avoid duplicate session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
}

// Check if user is authenticated and log logout event
if (isset($_SESSION['user_id'])) {
    $user = User::getAuthenticatedUser(); // Retrieve the current user

    // Log the logout event if the user exists
    if ($user) {
        try {
            $user->logUserLogout($user->id);
        } catch (Exception $e) {
            // Handle potential errors (e.g., logging failure)
            error_log("Logout log failed: " . $e->getMessage());
        }
    }
}

// Unset session variables and fully destroy the session
session_unset();
session_destroy();

// Redirect to login page
redirect('../login.php');
?>