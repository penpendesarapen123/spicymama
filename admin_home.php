    <?php
    // Guard
    require_once '_guards.php';
    Guard::adminAndManagerOnly();

    $suppliers = Supplier::all();
    $products = Product::all();
    $totalPrice = Product::getTotalPriceOfProducts();
    $outOfStockCount = Product::countOutOfStock();
    $lowStockCount = Product::countRunningLowStock();
    $fullStockCount = Product::countFullStock();
    $overStockCount = Product::countOverStock();
    $totalSuppliers = Supplier::getTotalSuppliers();

    function isBikeCategory($categoryName) {
        return in_array(strtolower($categoryName), ['bike', 'bicycle'], true);
    }
    
    function getStockStatus($quantity, $category) {
        $categoryName = strtolower($category->name); // Use product->category->name
        if (isBikeCategory($categoryName)) {
            // Custom thresholds for bike and bicycle
            if ($quantity == 0) {
                return '<i class="fas fa-times-circle"></i> No stock';
            } elseif ($quantity >= 1 && $quantity <= 10) {
                return '<i class="fas fa-exclamation-triangle"></i> Low stock';
            } elseif ($quantity >= 4 && $quantity <= 20) {
                return '<i class="fas fa-check-circle"></i> Fully stocked';
            } else { // Explicit condition for overstock
                return '<i class="fas fa-box"></i> Overstock';
            }
        } else {
            // Default thresholds for other categories
            if ($quantity == 0) {
                return '<i class="fas fa-times-circle"></i> No stock';
            } elseif ($quantity >= 1 && $quantity <= 10) {
                return '<i class="fas fa-exclamation-triangle"></i> Low stock';
            } elseif ($quantity >= 6 && $quantity <= 20) {
                return '<i class="fas fa-check-circle"></i> Fully stocked';
            } else {
                return '<i class="fas fa-box"></i> Overstock';
            }
        }
    }
    
    function getStockClass($quantity, $category) {
        $categoryName = strtolower($category->name); // Use product->category->name
        if (isBikeCategory($categoryName)) {
            // Custom thresholds for bike and bicycle
            if ($quantity == 0) {
                return "stock-no";
            } elseif ($quantity >= 1 && $quantity <= 10) {
                return "stock-low";
            } elseif ($quantity >= 4 && $quantity <= 20) {
                return "stock-full";
            } else { // Explicit condition for overstock
                return "stock-over";
            }
        } else {
            // Default thresholds for other categories
            if ($quantity == 0) {
                return "stock-no";
            } elseif ($quantity >= 1 && $quantity <= 10) {
                return "stock-low";
            } elseif ($quantity >= 6 && $quantity <= 20) {
                return "stock-full";
            } else {
                return "stock-over";
            }
        }
    }
    
    
    

    // Group products by name
    $groupedProducts = [];
    foreach ($products as $product) {
        $groupedProducts[$product->name][] = $product;
    }

    // Group products by category
    $categoryCounts = [];
    $categoryLabels = [];
    
    foreach ($products as $product) {
        // Exclude soft-deleted products or products under soft-deleted categories
        if ($product->deleted_at === null && $product->category->deleted_at === null) {
            if (!isset($categoryCounts[$product->category->name])) {
                $categoryCounts[$product->category->name] = 0;
                $categoryLabels[] = $product->category->name;
            }
            $categoryCounts[$product->category->name]++;
        }
    }

    $categoryCounts = array_values($categoryCounts);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Point of Sale System :: Home</title>
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

        <style>
         /* Disable horizontal scroll and allow vertical scroll */    
        body {
            overflow: hidden;
            
        } 
        .subtitle {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: -9px;
        }
        /* Updated hover effect for rows based on the stock status */
        .table-row {
            transition: background-color 0.3s ease;
        }

        /* Hover effects based on the stock status */
        .table-row.stock-no:hover {
            background-color: #f8d7da; /* Light red for out of stock */
        }

        .table-row.stock-low:hover {
            background-color: #fff3cd; /* Light yellow for low stock */
        }

        .table-row.stock-full:hover {
            background-color: #d4edda; /* Light green for full stock */
        }

        .table-row.stock-over:hover {
            background-color: #cce5ff; /* Light blue for overstock */
        }

        .table-responsive {
            max-width: 100%;
            overflow-x: auto; /* Allow horizontal scrolling on smaller screens */
        }

        table {
            width: 100%;
            border-collapse: collapse; /* Ensure no gaps between table cells */
        }

        td, th {
            color: #333;
            white-space: nowrap; /* Prevent text from wrapping, use ellipsis */
            overflow: hidden;
            text-overflow: ellipsis;
        }

        td {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        table th {
            background: #343a40;
            color: white;
        }

            /* Stock Status Colors */
        .stock-no {
            background-color: #e74c3c !important;
            color: white;
        }
        .stock-low {
            background-color: #f39c12 !important;
            color: white;
        }
        .stock-full {
            background-color: #27ae60 !important;
            color: white;
        }
        .stock-over {
            background-color: #2980b9 !important;
            color: white;
        }   
           
        .subtitle {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Adjusted styling for the action buttons container */
        .action-buttons {
            gap: 8px; /* Reduced space between buttons */
        }
        /* Improved Button Styles */
        .btn {
            border-radius: 4px;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
            color: white;
            transition: background-color 0.3s, transform 0.3s;
        }
    
        .btn-add-item {
            background-color: white;
            color: #28a745;
            border-color: #28a745;
            border-width: 2px;
        }

        .btn-category {
            background-color: white;
            color: #007bff;
            border-color: #007bff;
            border-width: 2px;
        }

        .btn-supplier {
            background-color: white;
            color: #6f42c1;
            border-color: #6f42c1;
            border-width: 2px;
        }

        .btn-restore {
            background-color: white;
            border-color: black;
            color: black;
            border-width: 2px;
        }

        .btn-logs{
            background-color: white;
            border-color: #895129;
            color: #895129;
            border-width: 2px;
        }
       #printInventoryReport{
            background-color: white;
            border-color: #808080;
            color: #895129;
            border-width: 2px;
       }
            
        .btn:hover {
            transform: scale(1.05);
            color: white;
        }

        .btn-add-item:hover {
            background-color: #28a745; /* Green for add */
            border-color: #28a745;
        }

        .btn-category:hover {
            background-color: #007bff; /* Blue for categories */
            border-color: #007bff;
        }

        .btn-supplier:hover {
            background-color: #6f42c1; /* Purple for suppliers */
            border-color: #6f42c1;
        }

        .btn-restore:hover {
            background-color: black; /* Purple for suppliers */
            border-color: black;
        }

        .btn-logs:hover {
            background-color: #895129; /* Purple for suppliers */
            border-color: #895129;
        }
        #printInventoryReport:hover{
            background-color: #808080; /* Purple for suppliers */
            border-color: #808080;
        }

        .card {
            padding: 15px; /* Reduced padding inside cards */
        }

        .small-card {
            width: 200%; /* Adjusted width to fit the container */
        }

        /* Reduced padding in the card for total price and status */
        .card-body {
            padding: 8px 12px; /* Decreased padding */
        }

        .card-title {
            font-weight: bold;
        }

        .chart-container {
            margin-left: auto;
            margin-right: auto;
        }

        @media(max-width: 992px){
            .chart-container {
            margin-left: auto;
            margin-right: auto;
            width: 100%;
        }
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
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
            <span class="subtitle me-auto">Inventory</span>
            <!-- Action Buttons -->
            <div class="action-buttons d-flex gap-2 mt-2 mt-md-0">
    <a href="admin_add_item.php" class="btn btn-add-item d-flex align-items-center justify-content-center">
        <i class="fas fa-plus-circle"></i>
        <span class="d-none d-md-inline ms-1">Add Item</span>
    </a>
    <a href="admin_category.php" class="btn btn-category d-flex align-items-center justify-content-center">
        <i class="fas fa-list"></i>
        <span class="d-none d-md-inline ms-1">Categories</span>
    </a>
    <a href="admin_suppliers.php" class="btn btn-supplier d-flex align-items-center justify-content-center">
        <i class="fas fa-truck"></i>
        <span class="d-none d-md-inline ms-1">Suppliers</span>
    </a>
    <a href="admin_logs.php" class="btn btn-logs d-flex align-items-center justify-content-center">
        <i class="fas fa-clipboard-list"></i>
        <span class="d-none d-md-inline ms-1">Logs</span>
    </a>
    <a href="admin_restore.php" class="btn btn-restore d-flex align-items-center justify-content-center">
        <i class="fas fa-trash-restore"></i>
        <span class="d-none d-md-inline ms-1">Archive</span>
    </a>
    <button id="printInventoryReport" class="btn btn-print-report d-flex align-items-center justify-content-center">
        <i class="fas fa-print"></i>
        <span class="d-none d-md-inline ms-1">Print Report</span>
    </button>
</div>



        </div>
        <hr/>


            <!-- Section Card for Total Price and Inventory Status -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <!-- Total Price Card -->
                <div class="col">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Price of All Products</h5>
                            <p class="card-text">₱<?= number_format($totalPrice, 2) ?></p>
                        </div>
                    </div>
                </div>
                <!-- Out of Stock Card -->
                <div class="col">
                    <div class="card text-white bg-danger text-center">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-times-circle"></i> Out of Stock</h5>
                            <p class="card-text"><?= htmlspecialchars($outOfStockCount) ?> products</p>
                        </div>
                    </div>
                </div>
                <!-- Low Stock Card -->
                <div class="col">
                    <div class="card text-white bg-warning text-center">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Low Stock</h5>
                            <p class="card-text"><?= htmlspecialchars($lowStockCount) ?> products</p>
                        </div>
                    </div>
                </div>
                <!-- Full Stock Card -->
                <div class="col">
                    <div class="card text-white bg-success text-center">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-check-circle"></i> Full Stock</h5>
                            <p class="card-text"><?= htmlspecialchars($fullStockCount) ?> products</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Product Count by Category Chart -->
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Product Count by Category</h5>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Stock Movement Chart -->
                <!-- Stock Movement Chart -->
<div class="col">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Stock Movement: New Items vs Add Stock vs Sold</h5>
            <div class="chart-container" style="position: relative; height: 300px;">
                <canvas id="stockAddDecreaseChart"></canvas>
            </div>
        </div>
    </div>
</div>

            </div>
            <hr>
                <?php displayFlashMessage('add_stock'); ?>
                <?php displayFlashMessage('delete_product'); ?>
            <div class="table-responsive">
                <!-- Data Table -->
                <table id="productsTable" class="display">
    <thead>
        <tr>
            <th>Product Code</th>
            <th>Name</th>
            <th>Category</th>
            <th>Stocks</th>
            <th>Price</th>
            <th>Size</th>
            <th>Supplier</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($groupedProducts as $products) : ?>
            <?php foreach ($products as $product) : ?>
                <?php
                $isService = $product->category_id == 24; // Check if the product is in the Services category
                $stockClass = getStockClass($product->quantity, $product->category);
                ?>
                <tr class="table-row <?= htmlspecialchars($stockClass) ?>"> <!-- Add hover effect class -->
                    <td><?= htmlspecialchars($product->product_code) ?></td>
                    <td><?= htmlspecialchars($product->name) ?></td>
                    <td><?= htmlspecialchars($product->category->name) ?></td>
                    <td><?= $isService ? 'N/A' : htmlspecialchars($product->quantity) ?></td>
                    <td>₱<?= number_format($product->price, 2) ?></td>
                    <td><?= $isService ? 'N/A' : htmlspecialchars($product->size) ?></td>

                    <td>
                        <?= $product->supplier ? htmlspecialchars($product->supplier->supplier_name) : 'No Supplier' ?>
                    </td>
                    <td class="<?= $isService ? '' : 'text-white ' . htmlspecialchars($stockClass) ?>">
                        <?= $isService ? 'No Status' : getStockStatus($product->quantity, $product->category) ?>
                    </td>
                    <td>
                        <?php if (!$isService): ?>
                            <a href="#" onclick="addStock(<?= $product->id ?>, '<?= htmlspecialchars($product->name, ENT_QUOTES) ?>', '<?= htmlspecialchars($product->product_code, ENT_QUOTES) ?>')" class="btn btn-success">
                                <i class="fa-solid fa-plus"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        <?php endif; ?>
                         <?php if ($currentUser && $currentUser->role === ROLE_ADMIN): ?>
                        <a href="admin_update_item.php?id=<?= htmlspecialchars($product->id) ?>" class="btn btn-primary">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                       
                        <a href="api/product_controller.php?action=delete&id=<?= $product->id ?>" class="btn btn-danger" onclick="return confirmDelete(event, <?= $product->id ?>);">
                            <i class="fa-solid fa-trash"></i>
                        </a> 
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

                </div>
                </div>
            </main>
        </div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="productCode" class="form-label">Product Code:</label>
                    <input type="text" class="form-control" id="productCode" readonly>
                </div>
                <div class="mb-3">
                    <label for="productName" class="form-label">Product Name:</label>
                    <input type="text" class="form-control" id="productName" readonly>
                </div>
            <form id="addStockForm" action="api/product_controller.php" method="GET">
                <input type="hidden" name="action" value="add_stock">
                <input type="hidden" name="id" id="productId">

                <div class="mb-3">
                    <label for="stockQuantity" class="form-label">Enter the Quantity to Add:</label>
                    <input type="number" class="form-control" id="stockQuantity" name="quantity" required>
                </div>
                <div class="mb-3">
                    <label for="supplierName" class="form-label">Supplier Name: </label>
                    <input type="text" class="form-control" id="supplierName" name="supplier" required>
                </div>
                <div class="mb-3">
                    <label for="receivedBy" class="form-label">Received By: </label>
                    <input type="text" class="form-control" id="receivedBy" name="received_by" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Stock</button>
                </div>
            </form>
            </div>
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

    <script type="text/javascript">

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
        var dataTable = new simpleDatatables.DataTable("#productsTable", {
            searchable: true,
        });

        // Chart.js Bar Graph
        var ctx = document.getElementById('categoryChart').getContext('2d');
        var categoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($categoryLabels) ?>,
                datasets: [{
                    label: 'Product Count',
                    data: <?= json_encode($categoryCounts) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        function confirmDelete(event, productId) {
        event.preventDefault(); // Prevent the immediate link navigation

        // First confirmation
        let firstConfirmation = confirm("Are you sure you want to delete this product?");
        
        if (firstConfirmation) {
            // Second confirmation
            let secondConfirmation = confirm("You can see the deleted products in the Archive");
            if (secondConfirmation) {
                // Redirect to the delete URL if both confirmations are confirmed
                window.location.href = `api/product_controller.php?action=delete&id=${productId}`;
            }
        }
    }

    function addStock(productId, productName, productCode) {
    // Set the hidden input field with the product ID
    document.getElementById('productId').value = productId;

    // Update the product name in the modal
    document.getElementById('productName').value = productName;
    document.getElementById('productCode').value = productCode;

    // Show the modal
    const addStockModal = new bootstrap.Modal(document.getElementById('addStockModal'));
    addStockModal.show();
}

 
    </script>

<script>
   document.addEventListener('DOMContentLoaded', function () {
    fetch('api/chart_controller.php?action=stock_add_decrease')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Process added, decreased, and new stock data
                const addedData = data.data.added;
                const decreasedData = data.data.decreased;
                const newItemsData = data.data.new_items;  // New items data

                // Extract unique dates and quantities
                const dates = [...new Set([
                    ...addedData.map(item => item.date),
                    ...decreasedData.map(item => item.date),
                    ...newItemsData.map(item => item.date),  // Include new items dates
                ])].sort();

                // Prepare the quantity data for each category
                const addedQuantities = dates.map(date =>
                    addedData.find(item => item.date === date)?.quantity || 0
                );

                const decreasedQuantities = dates.map(date =>
                    decreasedData.find(item => item.date === date)?.quantity || 0
                );

                const newItemsQuantities = dates.map(date =>
                    newItemsData.find(item => item.date === date)?.quantity || 0
                );

                // Create the chart
                const ctx = document.getElementById('stockAddDecreaseChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [
                            {
                                label: 'Added Stock',
                                data: addedQuantities,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                            },
                            {
                                label: 'Sold',
                                data: decreasedQuantities,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                            },
                            {
                                label: 'New Items',  // New dataset for new items
                                data: newItemsQuantities,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',  // Different color
                                borderColor: 'rgba(54, 162, 235, 1)',  // Different border color
                                borderWidth: 2,
                                tension: 0.3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date',
                                },
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Quantity',
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                        },
                    },
                });
            } else {
                console.error('Error fetching stock data:', data.error);
            }
        })
        .catch(error => console.error('Error:', error));
});


</script>

<script>
    document.getElementById('printInventoryReport').addEventListener('click', (event) => {
        event.preventDefault(); // Prevent default behavior
        window.open('models/generate_inventory_report.php', '_blank'); // Open in a new tab
    });
</script>

    </body>
    </html>
