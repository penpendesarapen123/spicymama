<?php
// Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

$product = Guard::hasModel(Product::class);
$categories = Category::all();
$sizes = isset($product->sizes) ? json_decode($product->sizes, true) : [];
$suppliers = Supplier::all();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Update Product</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-light">
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
                            Update Product
                        </h2>
                        
                        <!-- Empty Placeholder for Spacing -->
                        <div style="width: 50px;"></div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="api/product_controller.php?action=update&id=<?= htmlspecialchars($product->id) ?>" enctype="multipart/form-data">
                            <?php displayFlashMessage('update_product') ?>

                            <div class="mb-2">
                                <label for="product_code" class="form-label">Product Code</label>
                                <input 
                                    id="product_code"
                                    value="<?= htmlspecialchars($product->product_code) ?>" 
                                    type="text" 
                                    name="product_code" 
                                    class="form-control"
                                    required 
                                />
                            </div>

                            <div class="mb-2">
                                <label for="name" class="form-label">Name</label>
                                <input 
                                    id="name"
                                    value="<?= htmlspecialchars($product->name) ?>" 
                                    type="text" 
                                    name="name" 
                                    class="form-control"
                                    required 
                                />
                            </div>

                            <div class="mb-2">
                                <label for="category_id" class="form-label">Category</label>
                                <select id="category_id" name="category_id" class="form-select" required>
                                    <option value=""> -- Select Category --</option>
                                    <?php foreach ($categories as $category) : ?>
                                        <option 
                                            value="<?= $category->id ?>"
                                            <?= $category->id === $product->category_id ? 'selected' : '' ?>
                                        ><?= htmlspecialchars($category->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>



                            <div class="mb-2">
                                <label for="price" id="price_label" class="form-label">Price</label>
                                <input 
                                    id="price"
                                    value="<?= htmlspecialchars($product->price) ?>" 
                                    type="number" 
                                    step="0.01" 
                                    name="price" 
                                    class="form-control"
                                    required 
                                />
                            </div>

                            <!-- Size and Custom Size -->
                            <div class="mb-2">
    <label for="size_option" class="form-label">Size</label>
    <select name="size_option" id="size_option" class="form-select" required>
        <option value=""> -- Select Size --</option>
        <option value="Small" <?= $product->size === 'Small' ? 'selected' : '' ?>>Small</option>
        <option value="Medium" <?= $product->size === 'Medium' ? 'selected' : '' ?>>Medium</option>
        <option value="Large" <?= $product->size === 'Large' ? 'selected' : '' ?>>Large</option>
        <option value="Custom" <?= !in_array($product->size, ['Small', 'Medium', 'Large']) ? 'selected' : '' ?>>Custom</option>
    </select>
    <input 
        type="text" 
        name="custom_size" 
        id="custom_size_input" 
        class="form-control mt-2"
        placeholder="Enter size in CM, MM, Meters"
        value="<?= !in_array($product->size, ['Small', 'Medium', 'Large']) ? htmlspecialchars($product->size) : '' ?>" 
        style="display: <?= !in_array($product->size, ['Small', 'Medium', 'Large']) ? 'block' : 'none' ?>"
    />
</div>



                            <div class="mb-2">
                                <label for="image" class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" />
                                <?php if ($product->image): ?>
                                    <img src="<?= htmlspecialchars($product->image) ?>" width="80" class="img-thumbnail mt-2" alt="Product Image"/>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select id="supplier_id" name="supplier_id" class="form-select" required>
                                    <option value=""> -- Select Supplier --</option>
                                    <?php foreach ($suppliers as $supplier) : ?>
                                        <option 
                                            value="<?= $supplier->supplier_id ?>"
                                            <?= $supplier->supplier_id === $product->supplier_id ? 'selected' : '' ?>
                                        ><?= htmlspecialchars($supplier->supplier_name) ?></option>
                                    <?php endforeach; ?>
                                    <option value="NULL">None</option>
                                </select>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-primary w-100" type="submit">Update Product</button>
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
        const sizeField = document.getElementById('size');
        const customSizeInput = document.getElementById('custom_size_input');
        const priceLabel = document.getElementById('price_label');
        const supplierField = document.getElementById('supplier_id');

        // Check the initial category and apply changes if it's Services
        function handleServiceCategory() {
            if (categoryField.value === '24') { // Replace '24' with your Services category ID
                // Hide quantity field but ensure it has a default value
                quantityField.style.display = 'none';
                quantityInput.value = 10;

                // Set size to Custom, disable the dropdown, and display the custom size input
                sizeField.value = 'Custom';
                sizeField.setAttribute('readonly', true);
                customSizeInput.style.display = 'block';
                customSizeInput.value = 'N/A';

                // Change price label to indicate minimum price
                priceLabel.textContent = 'Set Minimum Price';

                // Set supplier to None and make it readonly
                supplierField.value = 'NULL';
                supplierField.setAttribute('readonly', true);
            } else {
                // Reset to default behavior for non-services categories
                quantityField.style.display = 'block';
                sizeField.removeAttribute('readonly');
                sizeField.value = '';
                customSizeInput.style.display = 'none';
                customSizeInput.value = '';
                priceLabel.textContent = 'Price';
                supplierField.removeAttribute('readonly');
            }
        }

        // Check on page load
        handleServiceCategory();

        // Reapply changes when the category selection changes
        categoryField.addEventListener('change', handleServiceCategory);

        // Show or hide custom size input based on size selection
        sizeField.addEventListener('change', function () {
            if (sizeField.value === 'Custom') {
                customSizeInput.style.display = 'block';
                customSizeInput.value = 'N/A';
            } else {
                customSizeInput.style.display = 'none';
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
    const sizeDropdown = document.getElementById('size_option');
    const customSizeInput = document.getElementById('custom_size_input');

    // Function to toggle custom size input visibility
    function toggleCustomSizeInput() {
        if (sizeDropdown.value === 'Custom') {
            customSizeInput.style.display = 'block';
        } else {
            customSizeInput.style.display = 'none';
            customSizeInput.value = ''; // Clear the custom input if not in use
        }
    }

    // Bind change event to dropdown
    sizeDropdown.addEventListener('change', toggleCustomSizeInput);

    // Check on page load (for pre-selected values)
    toggleCustomSizeInput();
});

</script>

</body>
</html>
