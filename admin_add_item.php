<?php
// Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

$categories = Category::all();
$suppliers = Supplier::all();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Add Product</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
        overflow: hidden;
    }
    .container {
        max-width: 700px;
        padding: 0;
    }
    .card {
        padding: 1rem;
        margin: 0;
    }
    .form-control, .form-select {
        font-size: 0.9rem;
        padding: 0.5rem;
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
    #custom_size_input {
    display: none; /* Hide by default */
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
        <main class="container my-3">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-lg rounded-3 border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <!-- Back Button -->
                        <a href="admin_home.php" class="btn btn-back d-flex align-items-center" 
                        style="text-decoration: none; color: #000; font-weight: bold;">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                        </a>

                        <!-- Centered Heading -->
                        <h2 class="text-primary text-center mb-0" style="flex-grow: 1;">
                            Add New Product
                        </h2>
                        
                        <!-- Empty Placeholder for Spacing -->
                        <div style="width: 50px;"></div>
                    </div>
                        <div class="card-body">
                            <form method="POST" action="api/product_controller.php?action=add" enctype="multipart/form-data">
                                <?php displayFlashMessage('add_product') ?>

                                <div class="mb-2">
                                    <label for="product_code" class="form-label">Product Code</label>
                                    <input type="text" name="product_code" id="product_code" class="form-control" required />
                                </div>

                                <div class="mb-2">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="name" id="name" class="form-control" required />
                                </div>

                                <div class="mb-2">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value=""> -- Select Category --</option>
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?= $category->id ?>"><?= htmlspecialchars($category->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-2" id="quantity_field">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" step="1" min="0" name="quantity" id="quantity" class="form-control" required />
                                </div>

                                <div class="mb-2">
                                    <label for="size_option" class="form-label">Size</label>
                                    <select name="size_option" id="size_option" class="form-select" required>
                                        <option value=""> -- Select Size --</option>
                                        <option value="Small">Small</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Large">Large</option>
                                        <option value="Custom">Custom</option>
                                    </select>
                                    <input type="text" name="custom_size" id="custom_size_input" class="form-control mt-2" placeholder="Enter size in CM, MM, Meters" />
                                </div>

                                <div class="mb-2">
                                    <label for="price" id="price_label" class="form-label">Price</label>
                                    <input type="number" step="0.25" name="price" id="price" class="form-control" required />
                                </div>

                                <div class="mb-2">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" name="image" id="image" class="form-control" accept="image/*" />
                                </div>

                                <div class="mb-2">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select name="supplier_id" id="supplier_id" class="form-select" required>
                                        <option value=""> -- Select Supplier --</option>
                                        <?php foreach ($suppliers as $supplier) : ?>
                                            <option value="<?= $supplier->supplier_id ?>">
                                                <?= htmlspecialchars($supplier->supplier_name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="NULL">None</option>
                                    </select>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary w-100" type="submit">Add Product</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
    
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
    document.addEventListener('DOMContentLoaded', function () {
        const categoryField = document.getElementById('category_id');
        const quantityField = document.getElementById('quantity_field');
        const quantityInput = document.getElementById('quantity');
        const sizeField = document.getElementById('size_option');
        const customSizeInput = document.getElementById('custom_size_input');
        const priceLabel = document.getElementById('price_label');
        const supplierField = document.getElementById('supplier_id');

        // Initially hide custom size input
        customSizeInput.style.display = 'none';

        categoryField.addEventListener('change', function () {
            if (categoryField.value === '24') { // Check if Services category is selected
                // Hide quantity field
                quantityField.style.display = 'none';
                // Set quantity to 10
                quantityInput.value = 10;

                // Set size to Custom and disable the dropdown
                sizeField.value = 'Custom';
                sizeField.setAttribute('readonly', true); // Make it readonly instead of disabled
                // Automatically display custom size input and set its value to N/A
                customSizeInput.style.display = 'block';
                customSizeInput.value = 'N/A';

                // Change price label
                priceLabel.textContent = 'Set Minimum Price';

                // Set supplier to None and disable the dropdown
                supplierField.value = 'NULL';
                supplierField.setAttribute('readonly', true);
            } else {
                // Reset form fields when a non-services category is selected
                quantityField.style.display = 'block';
                sizeField.removeAttribute('readonly');
                sizeField.value = '';
                customSizeInput.style.display = 'none';
                customSizeInput.value = '';
                priceLabel.textContent = 'Price';
                supplierField.removeAttribute('readonly');
            }
        });

        sizeField.addEventListener('change', function () {
            if (sizeField.value === 'Custom') {
                customSizeInput.style.display = 'block';
            } else {
                customSizeInput.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>
