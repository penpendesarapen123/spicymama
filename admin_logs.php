<?php
require_once '_guards.php'; 

Guard::adminManagerCashierOnly();  // Allow access for Admins, Managers, and Cashiers


$inventoryLogs = Product::inventoryLogs();
$newlyAddedItems = Product::getNewlyAddedItems();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale System :: Inventory Logs</title>
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
            color: black;
        }
        .card {
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 20px;
        }
        .table th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .btn-blue {
            background-color: #2977ed;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-blue:hover {
            background-color: #246BD5;
        }
        .btn-red {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-red:hover {
            background-color: #B02A37;
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
    <main class="flex-grow-1 p-4">
        <div class="d-flex align-items-center mb-3"> 
            <a href="admin_home.php" class="btn btn-back d-flex align-items-center me-3" 
                       style="text-decoration: none; color: #000; font-weight: bold;">
                        <i class="fa-solid fa-arrow-left me-1"></i>
                    </a>  
    <div class="subtitle m-0">Inventory Logs</div></div>
   
    <div class="col-md-12 mt-4">
    <div class="card">
        
        <div class="card-body">
                   
            
            <div class="d-flex align-items-center mb-3">
            <div class="subtitle m-0">Newly Added Products</div>
            </div>
            <hr/>
            <div class="table-responsive">
                <table id="newlyAddedItemsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Product Code</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Size</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newlyAddedItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_code']) ?></td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td><?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                                <td><?= htmlspecialchars($item['size']) ?></td>
                                <td><?= htmlspecialchars($item['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <!-- Flex container for back button and title -->
                <div class="d-flex align-items-center mb-3">
                <div class="subtitle m-0">Added Stock logs</div>
            </div>
                <hr/>
                <div class="table-responsive">
                <table id="inventoryLogsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty Added</th>
                            <th>Supplier Name</th>
                            <th>Received By</th>
                            <th>Date</th>
                            <th>Logged By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventoryLogs as $inventoryLog): ?>
                            <tr>
                                <td><?= htmlspecialchars($inventoryLog['product_name']) ?></td>
                                <td><?= htmlspecialchars($inventoryLog['quantity_added']) ?></td>
                                <td><?= htmlspecialchars($inventoryLog['supplier_name']) ?></td>
                                <td><?= htmlspecialchars($inventoryLog['received_by'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($inventoryLog['updated_at']) ?></td>
                                <td><?= htmlspecialchars($inventoryLog['updated_by'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

                </div>
            </div>
        </main>

<script src="./js/loader.js"></script>

<script>
        // Example: Show loader on page load and hide it after content is loaded
        showLoader();

        window.addEventListener('load', () => {
            hideLoader();
        });
    </script>

<script>
   var dataTable = new simpleDatatables.DataTable("#inventoryLogsTable", {
            searchable: true,
            fixedHeight: true
        });

        var dataTable = new simpleDatatables.DataTable("#newlyAddedItemsTable", {
            searchable: true,
            fixedHeight: true
        });

</script>
</body>
</html>
