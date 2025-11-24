<?php
// Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

$products = Product::getSoftDeleted();

$groupedProducts = [];
foreach ($products as $product) {
    $groupedProducts[$product->name][] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale System :: Archived Products</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/admin.css">
    <link rel="stylesheet" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            overflow: hidden;
        }
        .subtitle {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: -9px;
        }
        .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        table th {
            background: #343a40;
            color: white;
        }
        .restore-button {
            color: #28a745;
            cursor: pointer;
        }
        .section-title {
    display: flex;
    align-items: center;
    margin-top: 1px;
}

.btn-back {
    display: flex;
    align-items: center;
    margin-right: 10px; /* Adjust the margin as needed */
}
    </style>
</head>
<body>
    
    <?php require 'loader.php'; ?>
    
<?php require 'templates/admin_header.php' ?>



<div class="flex">
    <?php require 'templates/admin_navbar.php' ?>
    <main>
    <div class="wrapper w-60p">
    <div class="section-title">
    <a href="admin_home.php" class="btn btn-back d-flex align-items-center" 
                        style="text-decoration: none; color: #000; font-weight: bold;">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                        </a>
    <h2>Deleted Products</h2>
</div>
<?php if (isset($_GET['restore']) && $_GET['restore'] === 'success'): ?>
    <div id="successMessage" class="alert alert-success">
        Product restored successfully!
    </div>
<?php endif; ?>
    <hr>
    <div class="table-responsive">
        <table id="productsTable" class="display">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Stocks</th>
                        <th>Price</th>
                        <th>Size</th>
                        <th>Image</th>
                        <th>Supplier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php
                                $isService = $product->category_id == 24; // Check if the product is in the Services category 
                            ?>
                        <tr>
                            <td><?= htmlspecialchars($product->product_code) ?></td>
                            <td><?= htmlspecialchars($product->name) ?></td>
                            <td><?= htmlspecialchars($product->category->name ?? 'N/A') ?></td>
                            <td><?= $isService ? 'N/A' : htmlspecialchars($product->quantity) ?></td>
                            <td><?= htmlspecialchars(number_format($product->price, 2)) ?></td>
                            <td><?= $isService ? 'N/A' : htmlspecialchars($product->size) ?></td>
                            <td>
                                <?php if (!empty($product->image)): ?>
                                    <img src="<?= htmlspecialchars($product->image) ?>" alt="Image" style="max-width: 50px;">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product->supplier->supplier_name ?? 'No Supplier  ') ?></td>
                            <td>
                            <a href="models/restore_product.php?id=<?php echo $product->id; ?>" class="btn btn-success">
                                <i class="fas fa-trash-restore"></i> Restore
                            </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    var dataTable = new simpleDatatables.DataTable("#productsTable", {
        searchable: true,
    });

    document.addEventListener('DOMContentLoaded', function () {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(() => flashMessage.remove(), 500);
                }, 3000); // 3 seconds
            }
        });

    document.querySelectorAll('.restore-button').forEach(button => {
    button.addEventListener('click', function (event) {
        event.preventDefault(); // Prevent default action (e.g., navigation)
        const productId = this.dataset.id;

        if (confirm('Are you sure you want to restore this product?')) {
            fetch('models/restore_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: productId }), // Send the product ID as JSON
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message); // Show success message
                    location.reload(); // Reload the page
                } else {
                    alert(data.message); // Show error message
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('An unexpected error occurred.');
            });
        }
    });
});

</script>
</body>
</html>
