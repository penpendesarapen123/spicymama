<?php
//Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

require_once 'api/category_controller.php';

$categories = Category::all(true);

$category = null;


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Categories</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap JS (with Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        
        /* Global adjustments */
        body {
            overflow: hidden; 
        }
        
        main {
            padding-bottom: -200px; /* Adds spacing to move content down */
        }

        .category-form, .category-table {
            flex: 1;
            margin: 30px;
            padding: 25px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            min-width: 320px;
        }

        .category-form {
            max-width: 720px;
        }

        .category-table {
            flex-grow: 2;
        }

        .subtitle {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .form-control input {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        /* Improved button styling */
        .btn-custom  {
            background-image: linear-gradient(to right, #4c1a57, #00A8AA, #00A8AA, #4c1a57);
            border: none;
            color: white;
            padding: 12px 18px;
            font-size: 1.1rem;
            background-size: 300%;
            background-position: left;
            border-radius: 8px;
            font-weight: 600;
            transition: 300ms background-position ease-in-out;
            width: 100%;
            cursor: pointer;
        }

        .btn-custom:hover {
            background-position: right;
            color: #fff;
        }

       
        /* Table styling */
        .table {
            margin-top: 10px;
            width: 100%;
            font-size: 1rem;
        }

        .table th{
            padding: 16px;
            text-align: right;
            border-bottom: 1px solid #dee2e6;
        }

        .table th {
                background:  #343a40 ;
                color: white; /* Set text color to white for contrast */
        }

        .table tr:hover {
            background-color: #f9f9f9;
        }

        .table a {
            color: #007bff;
            font-weight: bold;
        }

        /* Spacing and alignment improvements */
        .category-table {
            max-width: 100%;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }
        a {
            text-decoration: none;
        }
    </style>

</head>
<body>
    
    <?php require 'loader.php'; ?>

    <?php require 'templates/admin_header.php' ?>

    <div class="d-flex flex-column flex-lg-row">
        <?php require 'templates/admin_navbar.php' ?>
        <main class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between">
                <div class="category-form">
                    <span class="subtitle">
                    <a href="admin_home.php" class="btn btn-back" 
                        style="text-decoration: none; color: #000; font-weight: bold;">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                        </a>
                        New Category
                    </span>
                    <hr/>

                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="api/category_controller.php">

                                <input type="hidden" name="action" value="<?= get('action') === 'update' ? 'update' : 'add' ?>" />
                                <input type="hidden" name="id" value="<?= $category?->id ?>"/>

                                <div class="form-group">
                                    <label for="categoryName">Category Name</label>
                                    <input 
                                        value="<?= $category?->name ?>" 
                                        type="text" 
                                        name="name" 
                                        id="categoryName" 
                                        class="form-control"
                                        placeholder="Enter category name here" 
                                        required
                                    />
                                </div>

                                <!-- Updated button with gradient and hover effect -->
                                <button class="btn btn-custom" type="submit">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="category-table">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="subtitle ">Category List</span>
                    <button id="toggleDeletedBtn" class="btn btn-secondary">
                    <i class="fas fa-trash-restore"></i> Show Deleted</button>
                    </div>
                    
                    <hr/>

                    <?php displayFlashMessage('add_category') ?>
                    <?php displayFlashMessage('delete_category') ?>
                    <?php displayFlashMessage('update_category') ?>
                    <?php displayFlashMessage('restore_category')?>
                    <div class="table-responsive">
                    <table id="categoryTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr class="<?= $category->deleted_at ? 'deleted-category' : '' ?>">
                                <td>
                                    <?= $category->deleted_at ? '<s>' . htmlspecialchars($category->name) . '</s>' : htmlspecialchars($category->name) ?>
                                </td>
                                <td>
                                    <?php if ($category->deleted_at): ?>
                                    <!-- Restore Button -->
                                        <a class="text-success ms-3" href="api/category_controller.php?action=restore&id=<?= $category->id ?>" onclick="return confirm('Are you sure you want to restore this category?');">
                                            <button class="btn btn-success btn-sm"><i class="fa-solid fa-undo"></i></button>
                                        </a>
                                    <?php else: ?>
                                        <!-- Edit Button -->
                                        <button class="btn btn-primary btn-sm" onclick="openUpdateModal(<?= $category->id ?>, '<?= htmlspecialchars($category->name) ?>')"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <!-- Delete Button -->
                                        <a class="text-danger ms-3" href="api/category_controller.php?action=delete&id=<?= $category->id ?>" onclick="return confirm('Are you sure you want to delete this category?');">
                                            <button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
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

    <!-- Update Category Modal -->
<div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateCategoryModalLabel">Update Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateCategoryForm" method="POST" action="api/category_controller.php">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" id="modalCategoryId" name="id" value=""/>
                    <div class="form-group">
                        <label for="modalCategoryName">Category Name</label>
                        <input type="text" class="form-control" id="modalCategoryName" name="name" placeholder="Enter category name" required />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
    // Initialize Datatable
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Datatable
        var dataTable = new simpleDatatables.DataTable("#categoryTable", {
            searchable: true
        });

        // Toggle Deleted Button Logic
        const toggleDeletedBtn = document.getElementById("toggleDeletedBtn");
        const categoryTableBody = document.querySelector("#categoryTable tbody");
        let showDeleted = false;

        toggleDeletedBtn.addEventListener("click", () => {
            showDeleted = !showDeleted;
            toggleDeletedBtn.textContent = showDeleted ? "Hide Deleted" : "Show Deleted";

            // Loop through table rows and toggle visibility based on 'deleted' status
            const rows = categoryTableBody.querySelectorAll("tr");
            rows.forEach(row => {
                const isDeleted = row.querySelector("td").innerHTML.includes("<s>"); // Check for strikethrough
                if (isDeleted) {
                    row.style.display = showDeleted ? "" : "none"; // Show or hide soft-deleted rows
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        // Hide all rows with the class 'deleted-category' initially
        const deletedRows = document.querySelectorAll(".deleted-category");
        const toggleDeletedBtn = document.getElementById("toggleDeletedBtn");
        const toggleIcon = toggleDeletedBtn.querySelector("i"); // Get the icon element

        deletedRows.forEach(row => {
            row.style.display = "none"; // Hide rows by default
        });

        // Set button to show deleted by default
        let showingDeleted = false;

        // Add event listener to the toggle button
        toggleDeletedBtn.addEventListener("click", function () {
            showingDeleted = !showingDeleted;

            if (showingDeleted) {
                // Show deleted rows and update button text and icon
                deletedRows.forEach(row => {
                    row.style.display = ""; // Empty string resets display to default
                });
                toggleDeletedBtn.textContent = " Hide Deleted";
                toggleDeletedBtn.prepend(toggleIcon);
                toggleIcon.className = "fas fa-eye-slash"; // Change to "hide" icon
            } else {
                // Hide deleted rows and update button text and icon
                deletedRows.forEach(row => {
                    row.style.display = "none";
                });
                toggleDeletedBtn.textContent = " Show Deleted";
                toggleDeletedBtn.prepend(toggleIcon);
                toggleIcon.className = "fas fa-trash-restore"; // Change to "restore" icon
            }
        });
    });

        // Function to open the update modal and populate fields
function openUpdateModal(categoryId, categoryName) {
    document.getElementById('modalCategoryId').value = categoryId; // Set the category ID
    document.getElementById('modalCategoryName').value = categoryName; // Set the category name
    // Show the modal
    const updateModal = new bootstrap.Modal(document.getElementById('updateCategoryModal'));
    updateModal.show();
}

</script>
