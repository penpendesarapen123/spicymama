<?php
require_once __DIR__ . '/../_init.php';

if (get('action') === 'add') {
    $name = post('name');
    $email = post('email');
    $role = post('role');
    // Generate a default password
    $password = strtolower(str_replace(' ', '', $name)) . '123';

    try {
        // Validate input fields
        if (empty($name) || empty($email) || empty($role) || empty($password)) {
            flashMessage('add_user', 'All fields are required.', 'danger');
            redirect('../admin_add_account.php');
            exit();
        }

        // Check if email already exists
        if (User::emailExists($email)) {
            flashMessage('add_user', 'This email is already registered.', 'danger');
            redirect('../admin_add_account.php');
            exit();
        }

        // Check if the name already exists
        if (User::nameExists($name)) {
            flashMessage('add_user', 'The name is already in use. Please choose a different name.', 'danger');
            redirect('../admin_add_account.php');
            exit();
        }

        // Add the new user
        User::add($name, $email, $role, $password);

        flashMessage('add_user', 'Account added successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        flashMessage('add_user', 'An error occurred: ' . $ex->getMessage(), 'danger');
    }

    redirect('../admin_add_account.php');
}

// Handle deletion of a user
if (get('action') === 'delete') {
    $id = get('id');

    User::find($id)?->archive();

    flashMessage('delete_user', 'Account archived successfully.', FLASH_SUCCESS);
    redirect('../admin_accounts.php');
}

if (get('action') === 'restore') {
    $id = get('id');
    $stmt = $connection->prepare('UPDATE `users` SET is_archived = 0 WHERE id = :id');
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    flashMessage('restore_user', 'Account restored successfully.', FLASH_SUCCESS);
    redirect('../admin_accounts.php');
}

// Handle updating user accounts
if (get('action') === 'update') {
    $users = Guard::hasModel(User::class);
    $newEmail = post('email');
    $name = post('name');
    $role = post('role');

    try {
        // Check if email is already in use by another user
        if (User::emailExists($newEmail, $users->id)) {
            flashMessage('update_user', 'This email is already registered with another account.', 'danger');
            redirect('../admin_update_account.php?id=' . $users->id);
            exit();
        }

        // Update user details
        $users->name = $name;
        $users->email = $newEmail;
        $users->role = $role;

        $users->update();
        flashMessage('update_user', 'Account updated successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        flashMessage('update_user', 'An error occurred while updating the account: ' . $ex->getMessage(), 'danger');
    }

    redirect('../admin_update_account.php?id=' . $users->id);
}

// Handle updating user passwords
if (get('action') === 'updatePassword') {
    $userId = get('id');
    $newPassword = post('password');

    try {
        // Validate input
        if (empty($newPassword)) {
            throw new Exception('Password cannot be empty.');
        }

        // Find the user
        $user = User::find($userId);
        if (!$user) {
            throw new Exception('User not found.');
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password
        $user->password = $hashedPassword;
        $user->update();

        // Clear the flag once the password is updated
        unset($_SESSION['require_password_change']);

        // Success message
        flashMessage('update_password', 'Password updated successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        flashMessage('update_password', 'An error occurred: ' . $ex->getMessage(), 'danger');
    }

    redirect('../index.php'); // Redirect to a relevant page
}

?>
