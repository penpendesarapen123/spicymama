<?php
require_once '_init.php';
require_once '_guards.php';
Guard::adminAndManagerOnly();

$products = Product::all();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale System :: Sales by Product</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/admin.css">
    <link rel="stylesheet" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
        color: #212529;
    }

    .flex {
        display: flex;
        min-height: 100vh;
    }

    main {
        flex: 1;
        padding: 20px;
        background-color: #ffffff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #343a40;
    }

    hr {
        border: 0;
        border-top: 1px solid #dee2e6;
        margin: 20px 0;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #dee2e6;
    }

    .table thead {
        background-color: #343a40;
        color: #ffffff;
    }

    .table tbody tr:hover {
        background-color: #f1f3f5;
    }

    .btn-primary {
        display: inline-block;
        padding: 10px 20px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 4px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    img {
        max-width: 50px;
        height: auto;
        border-radius: 4px;
        object-fit: cover;
    }

    .table-row input[type="checkbox"] {
        cursor: pointer;
    }
</style>
</head>
<body>

<?php require 'loader.php'; ?>
<?php require 'templates/admin_header.php'; ?>

<div class="flex">
    <?php require 'templates/admin_navbar.php'; ?>
    <main>
        <div class="container">
            <h1>Sales by Product</h1>
            <hr>

            <form id="productForm" method="POST" action="models/getSalesProduct.php">
                <table class="table table-bordered table-striped" id="salesByProduct">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Product Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr class="table-row">
                                    <td>
                                        <input type="checkbox" name="product_ids[]" value="<?= htmlspecialchars($product->id) ?>">
                                    </td>
                                    <td><?= htmlspecialchars($product->product_code) ?></td>
                                    <td><?= htmlspecialchars($product->name) ?></td>
                                    <td><?= htmlspecialchars($product->category->name) ?></td>
                                    <td>
                                        <?php if ($product->image): ?>
                                            <img src="<?= htmlspecialchars($product->image) ?>" alt="Product Image">
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <hr>

                <button type="submit" id="generateReportBtn" class="btn-primary">Generate Sales Report</button>
            </form>
        </div>
    </main>
</div>

<script>
    // DataTable initialization
    var dataTable = new simpleDatatables.DataTable("#salesByProduct", {
        searchable: true,
        fixedHeight: true
    });

    // Select/Deselect All Checkboxes
    document.getElementById('generateReportBtn').addEventListener('click', function (event) {
    event.preventDefault(); // Prevent the default form submission

    const form = document.getElementById('productForm');
    const formData = new FormData(form);
    const productIds = [];

    // Collect selected product IDs
    formData.forEach((value, key) => {
        if (key === 'product_ids[]') {
            productIds.push(value);
        }
    });

    if (productIds.length === 0) {
        alert('Please select at least one product.');
        return;
    }

    // Open a new tab to generate the PDF
    const params = new URLSearchParams({ product_ids: JSON.stringify(productIds) });
    window.open('models/getSalesProduct.php?' + params.toString(), '_blank');
});

</script>

</body>
</html>
