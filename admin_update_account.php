<?php
// Guard
require_once '_guards.php';


$users = Guard::hasModel(User::class);



?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Update Account</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-light">
    
    <?php require 'loader.php'; ?>

    <?php require 'templates/admin_header.php' ?>
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
    }
    .btn {
        padding: 0.5rem 1rem;
    }
    .card-header{
        background-color: white;
    }
    </style>
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
                           Update Account
                        </h2>
                        
                        <!-- Empty Placeholder for Spacing -->
                        <div style="width: 50px;"></div>
                    </div>

                    <div class="card-body">
                            <form method="POST" action="api/account_controller.php?action=update&id=<?= $users->id ?>">
                                <?php displayFlashMessage('update_user') ?>

                                <div class="mb-2">
                                    <label for="name" class="form-label">Name</label>
                                    <input 
                                        id="name"
                                        class="form-control"
                                        value="<?= $users->name ?>" 
                                        type="text" 
                                        name="name" 
                                        required 
                                    />
                                </div>

                                <div class="mb-2">
                                    <label for="email" class="form-label">Email</label>
                                    <input 
                                        id="email"
                                        class="form-control"
                                        value="<?= $users->email ?>"
                                        name="email"
                                        type="email"
                                        required
                                    />     
                                </div>

                                <div class="mb-2">
                                    <label for="role" class="form-label">Role</label>
                                    <select 
                                        id="role" 
                                        class="form-select" 
                                        required 
                                        name="role"
                                    >
                                    <option value="ADMIN" <?= $users->role === 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
                                    <option value="MANAGER" <?= $users->role === 'MANAGER' ? 'selected' : '' ?>>MANAGER</option>
                                    <option value="CASHIER" <?= $users->role === 'CASHIER' ? 'selected' : '' ?>>CASHIER</option>

                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label for="password" class="form-label">Password</label>
                                    <input  
                                        id="password"
                                        class="form-control"
                                        required 
                                        type="password" 
                                        name="password"
                                        oninput="checkPasswordStrength()" 
                                    />
                                    <div id="passwordStrength" class="form-text"></div>
                                </div>

                                <div class="mb-2">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input 
                                        id="confirmPassword"
                                        type="password" 
                                        name="confirmPassword" 
                                        class="form-control"
                                        required 
                                        oninput="checkPasswordMatch()"
                                    />
                                    <div id="passwordMatch" class="form-text"></div>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary w-100" type="submit">Update Account</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

<script src="./js/loader.js"></script>

<script>
        // Example: Show loader on page load and hide it after content is loaded
        showLoader();

        window.addEventListener('load', () => {
            hideLoader();
        });
    </script>
</html>
