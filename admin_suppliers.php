<?php
require_once '_guards.php';
require_once 'models/Supplier.php'; // Adjust the path if needed
Guard::adminAndManagerOnly();


// Fetch suppliers from the database
$showDeleted = isset($_GET['showDeleted']) && $_GET['showDeleted'] === 'true';

// Fetch suppliers from the database
$suppliers = Supplier::all($showDeleted);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Suppliers</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

     <!-- Datatables Library -->
     <link rel="stylesheet" type="text/css" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

    <!-- Ensure only content area is styled -->
    <style>
         body {
            overflow: hidden;
        } 
       
        /* Apply styles only to the content area */
        .table-container {
            background-color: white;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .subtitle {
            font-size: 1.5rem;
            font-weight: bold;
            color: #343a40;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }

        /* Button transition effects */
        button.btn {
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        /* Modal styling */
        .modal-content {
            border-radius: 8px;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
        }

        /* No styles leaking from content to sidebar or navbar */
    </style>
<body>
    
    <?php require 'loader.php'; ?>
    
    <?php require 'templates/admin_header.php' ?>  <!-- The header remains untouched -->

    <div class="d-flex flex-column flex-lg-row">
        <?php require 'templates/admin_navbar.php' ?>  <!-- Sidebar remains untouched -->

        <main class="flex-grow-1 p-4">
            <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="subtitle">
            <a href="admin_home.php" class="btn btn-back">
            <i class="fa-solid fa-arrow-left me-1"></i>
            </a> Supplier List
            </span>
            <div class="d-flex gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    <i class="fas fa-plus"></i><span class="d-none d-md-inline ms-1">Add Item</span>
                </button>
                <button id="toggleDeletedBtn" class="btn btn-secondary">
                <i class="fas fa-trash-restore"></i> Show Deleted
                    </button>               
                </div>
            </div>

                <hr/>
            <div class="table-responsive">
                <table id="supplierTable" class="table table-hover table-striped">
                    <?php displayFlashMessage('supplier_added'); ?>
                    <?php displayFlashMessage('supplier_updated'); ?>
                    <?php displayFlashMessage('supplier_deleted'); ?>
                    <?php displayFlashMessage('supplier_restored'); ?>
                    <thead class="table-dark">
                        <tr>
                            <th>Supplier Name</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr <?= $supplier->deleted_at ? 'style="text-decoration: line-through;"' : '' ?>>
                            <td><?= htmlspecialchars($supplier->supplier_name) ?></td>
                            <td><?= htmlspecialchars($supplier->phone_number) ?></td>
                            <td><?= htmlspecialchars($supplier->address) ?></td>
                            <td><?= htmlspecialchars($supplier->created_date) ?></td>
                            <td>
                            <?php if ($supplier->deleted_at): ?>
                                <a href="api/supplier_controller.php?action=restore&id=<?= $supplier->supplier_id ?>" 
                                onclick="return confirm('Restore this supplier?');">
                                    <button class="btn btn-sm btn-success"><i class="fas fa-undo"></i></button>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-primary" 
                                        onclick="populateEditModal(<?= htmlspecialchars(json_encode($supplier)) ?>)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="api/supplier_controller.php?action=delete&id=<?= $supplier->supplier_id ?>" 
                                onclick="return confirm('Delete this supplier?');">
                                    <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </a>
                            <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </main>
    </div>

    <!-- Modal for adding new supplier -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="api/supplier_controller.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="supplier_name" class="form-label">Supplier Name</label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" required maxlength="15" pattern="\d{10,15}" title="Enter 10 to 15 digits" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for editing a supplier -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="api/supplier_controller.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_supplier_id" name="supplier_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_supplier_name" class="form-label">Supplier Name</label>
                            <input type="text" class="form-control" id="edit_supplier_name" name="supplier_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit_phone_number" name="phone_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="edit_address" name="address" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<script src="./js/loader.js"></script>

<script>
        // Example: Show loader on page load and hide it after content is loaded
        showLoader();

        window.addEventListener('load', () => {
            hideLoader();
        });
    </script>
    <!-- Bootstrap and JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        var dataTable = new simpleDatatables.DataTable("#supplierTable", {
            searchable: true,
        });

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
        // Populate the edit modal with the supplier data
        function populateEditModal(supplier) {
            document.getElementById('edit_supplier_id').value = supplier.supplier_id;
            document.getElementById('edit_supplier_name').value = supplier.supplier_name;
            document.getElementById('edit_phone_number').value = supplier.phone_number;
            document.getElementById('edit_address').value = supplier.address;

            // Show the edit modal
            var editModal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
            editModal.show();
        }

         document.addEventListener("DOMContentLoaded", function () {
            const toggleDeletedBtn = document.getElementById("toggleDeletedBtn");

            // Initialize the button text based on the URL parameter
            const params = new URLSearchParams(window.location.search);
            const showDeleted = params.get('showDeleted') === 'true';

            toggleDeletedBtn.innerHTML = showDeleted
                ? '<i class="fas fa-eye-slash"></i> Hide Deleted'
                : '<i class="fas fa-trash-restore"></i> Show Deleted';

            // Add event listener to toggle deleted suppliers
            toggleDeletedBtn.addEventListener("click", function () {
                params.set('showDeleted', !showDeleted);
                window.location.search = params.toString();
            });
        });
    </script>
</body>
</html>
