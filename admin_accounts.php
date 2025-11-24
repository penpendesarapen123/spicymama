<?php
// Guard
require_once '_guards.php';
require_once '_init.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the User class if not already included
require_once 'models/User.php';

try {
    $users = User::all();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit(); // Exit the script if an error occurs
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Users</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
     body {
        overflow: hidden;
            
    } 
    .subtitle {
        font-size: 1.4rem;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .table th {
        background-color: #343a40;
        color: white;
    }
    @media (max-width: 1264px) {
        .btn-text {
            display: none; /* Hide the text on screens smaller than 768px */
        }
    }

</style>
<body>


    <?php require 'templates/admin_header.php' ?>

    <div class="flex">
        <?php require 'templates/admin_navbar.php' ?>
        <main>
            <div class="wrapper">
                <div class="w-100">
                    <div class="subtitle">Users</div>
                    <hr/>
                    <?php displayFlashMessage('delete_user'); // Display delete message ?>
                    <div class="table-responsive">
                        <table id="userTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="d-none d-sm-table-cell">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th> <!-- New column for status -->
                                    <th style="text-align: center">
                                        <a href="admin_add_account.php">
                                            <button class="btn btn-success btn-sm"><i class="fa-solid fa-plus"></i> <span class="btn-text">New User</span></button>
                                        </a> 
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php error_log("User ID: {$user->id}, Is Archived: {$user->is_archived}"); ?>
                                    <tr class="<?= $user->is_archived ? 'table-secondary' : '' ?>"> <!-- Highlight archived rows -->
                                        <td class="d-none d-sm-table-cell"><?= htmlspecialchars($user->id) ?></td>
                                        <td><?= htmlspecialchars($user->name) ?></td>
                                        <td><?= htmlspecialchars($user->email) ?></td>
                                        <td><?= htmlspecialchars($user->role) ?></td>
                                        <td>
                                            <?= $user->is_archived ? '<span class="badge bg-secondary">Inactive</span>' : '<span class="badge bg-success">Active</span>' ?>
                                        </td>
                                        <td style="width: 10%; text-align: center;">
                                            <?php if ($user->is_archived): ?>
                                                <!-- Restore button -->
                                                <a href="api/account_controller.php?action=restore&id=<?= htmlspecialchars($user->id) ?>" 
                                                style="text-decoration: none;" onclick="return confirm('Are you sure you want to set this user as active again?');">
                                                    <button class="btn btn-success btn-sm"><i class="fa-solid fa-undo"></i></button>
                                                </a>
                                            <?php else: ?>
                                                <!-- Edit and Archive buttons -->
                                                <a href="admin_update_account.php?id=<?= htmlspecialchars($user->id) ?>" style="text-decoration: none;">
                                                    <button class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i></button>
                                                </a>
                                                <a href="api/account_controller.php?action=delete&id=<?= htmlspecialchars($user->id) ?>" 
                                                onclick="return confirm('Are you sure you want to set this user as inactive?');">
                                                    <button class="btn btn-danger btn-sm"><i class="fas fa-lock"></i></i></button>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
<script>
    function confirmDelete(event, userId) {
        event.preventDefault(); // Prevent the default link behavior

        // First confirmation
        let firstConfirmation = confirm("Are you sure you want to archive this account?");
        
        if (firstConfirmation) {
            // Second confirmation
            let secondConfirmation = confirm("Account can be restored again");
            if (secondConfirmation) {
                // Redirect to the delete URL if both confirmations are confirmed
                window.location.href = `api/account_controller.php?action=delete&id=${userId}`;
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Select the flash message
        const flashMessage = document.querySelector('.alert');
        
        // If there's a flash message, set a timeout to hide it
        if (flashMessage) {
            setTimeout(() => {
                flashMessage.style.transition = 'opacity 0.5s ease';
                flashMessage.style.opacity = '0';
                
                // Remove flash message from the DOM after it fades out
                setTimeout(() => flashMessage.remove(), 500);
            }, 3000); // 3-second delay before hiding
        }
    });

    var dataTable = new simpleDatatables.DataTable("#userTable", {
        searchable: true
    });
</script>
