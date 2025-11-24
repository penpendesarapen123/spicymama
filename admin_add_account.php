<?php
// Guard
require_once '_guards.php';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Create Account</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    <style>
    body {
        overflow: hidden;
    }
    .card {
        padding: 1rem;
        margin: 0;
    }
    .card-body {
        padding: 1rem;
    }
    h2 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        text-align: center;
    }
    .btn {
        padding: 0.5rem 1rem;
    }
    .card-header{
        background-color: white;
    }
    </style>
</head>
<body class="bg-light">
    
    <?php require 'loader.php'; ?>

    <?php require 'templates/admin_header.php' ?>

    <div class="d-flex">
        <?php require 'templates/admin_navbar.php' ?>
        <main class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg rounded-3 p-4 border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <!-- Back Button -->
                        <a href="admin_accounts.php" class="btn btn-back d-flex align-items-center" 
                        style="text-decoration: none; color: #000; font-weight: bold;">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                        </a>
                        <!-- Centered Heading -->
                        <h2 class="text-primary text-center mb-0" style="flex-grow: 1;">
                           Add New Account
                        </h2>
                        <!-- Empty Placeholder for Spacing -->
                        <div style="width: 50px;"></div>
                    </div>

                        <div class="card-body">
                            <form method="POST" action="api/account_controller.php?action=add">
                                <?php displayFlashMessage('add_user'); ?>

                                <div class="mb-2">
                                    <label for="name" class="form-label">Name</label>
                                    <input id="name" type="text" name="name" class="form-control" required />
                                </div>

                                <div class="mb-2">
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" name="email" type="email" class="form-control" required />     
                                </div>

                                <div class="mb-2">
                                    <label for="role" class="form-label">Role</label>
                                    <select 
                                        name="role" id="role" class="form-select" 
                                        required>
                                        <option value="">Select a role</option>
                                        <option value="ADMIN">ADMIN</option>
                                        <option value="MANAGER">MANAGER</option>
                                        <option value="CASHIER">CASHIER</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label for="password" class="form-label">Password</label>
                                    <input id="password" type="password" name="password" class="form-control"required oninput="checkPasswordStrength(); checkPasswordMatch();"/>
                                    <div id="passwordStrength" class="form-text"></div>
                                </div>

                                <div class="mb-2">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input id="confirmPassword" type="password" name="confirmPassword" class="form-control" required oninput="checkPasswordMatch()"/>
                                    <div id="passwordMatch" class="form-text"></div>
                                </div>


                                <div class="mt-3">
                                    <button class="btn btn-primary w-100" type="submit">Add Account</button>
                                </div>
                            </form>
                        </div><!--end card-body-->
                    </div><!--end card-->
                </div><!--end col-->
            </div><!--end row-->
        </main>
    </div>

</body>
</html>

<script src="./js/loader.js"></script>

<script>
        // Example: Show loader on page load and hide it after content is loaded
        showLoader();

        window.addEventListener('load', () => {
            hideLoader();
        });
    </script>
    
<script>

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

    
</script>
