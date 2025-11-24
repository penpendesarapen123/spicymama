<?php
require_once __DIR__ . '/../_init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(post('email'));
    $password = trim(post('password'));

    try {
        // Fetch user details by email
        $user = User::findByEmail($email);
 
        if (!$user) {
            // If user is not found
            throw new Exception('Invalid email or password.');
        }

        // Check if the user is archived
        if ($user->is_archived) {
            throw new Exception('This account is inactive. Please contact the administrator.');
        }

        // Verify the password
        if (!password_verify($password, $user->password)) {
            throw new Exception('Invalid password.');
        }

        // Start a session for the user
        session_start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['role'] = $user->role;

        // Check if the password is default or if a password change is required
        $defaultPassword = strtolower(str_replace(' ', '', $user->name)) . '123';
        if (password_verify($defaultPassword, $user->password) || $user->password_change_required) {
            $_SESSION['require_password_change'] = true; // Show the modal
        } else {
            unset($_SESSION['require_password_change']); // Clear the flag if not required
        }

        // Record the login activity
        $user->logUserLogin($user->id, $user->role);

        // Redirect user to their role-specific homepage
        redirect('../' . $user->getHomePage());
    } catch (Exception $e) {
        // Handle login errors
        flashMessage('login', $e->getMessage(), FLASH_ERROR);
        redirect('../login.php');
    }
} else {
    // Redirect if accessed without a POST request
    redirect('../login.php');
}