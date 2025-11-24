<?php
// Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

$transactions = OrderItem::all();


$currentUser = User::getAuthenticatedUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale System :: Sales</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/admin.css">
    <link rel="stylesheet" href="./css/util.css">
    <link rel="stylesheet" href="./css/datatable.css">
    <link rel="stylesheet" href="./css/uidatepicker.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



</head>

<style>
    
    /* Body and main content styles */
    body {
        background-color: #f4f6f9;
        overflow:hidden;
    }
    /* Sales container and card styles */
    .sales-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
    }
    .sales-info-card {
        flex: 1;
        min-width: 200px;
        border-radius: 10px;
        color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .today-sales-card { background-color: #007bff; }
    .monthly-sales-card { background-color: #28a745; }
    .annual-sales-card { background-color: #ffc107; }
    .total-sales-card { background-color: #dc3545; }
    .card-header { font-weight: bold; }
    .card-body { font-size: 1.8rem; font-weight: bold; text-align: center; }
    /* Table styling */
    .table {
        width: 100%;
        margin-bottom: 1rem;
        background-color: white;
    }
    .table th, .table td { padding: 15px; }
    .table th { background-color: #343a40; color: white; }
    /* Calendar and heatmap legend */
    #calendar {
        width: 250;
        margin: 0 auto;
    }
    .calendar-container { 
        display: flex;
        flex-wrap: wrap; /* Allow wrapping on smaller screens */
        justify-content: center; /* Center-align items */
        gap: 10px; /* Add space between calendar and legend */
        margin-bottom: 20px;
    }

    #calendar td {
        min-width: 40px; /* Ensure cells have a minimum width */
        min-height: 40px; /* Ensure cells have a minimum height */
        text-align: center;
        font-size: calc(0.8rem + 0.2vw); /* Responsive font size */
        word-wrap: break-word; /* Wrap large text inside cells */
        overflow: hidden; /* Prevent overflow */
    }

    .heatmap-legend {
        flex: 1; /* Take up equal width with the calendar */
        min-width: 250px; /* Ensure it doesn’t shrink too much */
        max-width: auto; /* Set a maximum width */
        margin-top: 10px; /* Space above */
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .color-box {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        margin-right: 10px;
    }

    .date-picker-container {
        display: flex;
        flex-wrap: wrap; /* Wrap on smaller screens */
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .date-input-group {
        display: flex;
        flex-direction: column;
    }

    #fetchSalesDataBtn, #printChartBtn {
        flex-shrink: 0; /* Prevent buttons from shrinking */
        padding: 5px 10px; /* Adjust padding for smaller screens */
        font-size: 0.9rem;
    }

    .btn-group{
        gap: 10px;
    }

    .modal-body {
        max-height: 500px;
        overflow-y: auto;
    }
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    #salesChart {
        width: 100%; /* Full width */
        height: 400px; /* Set an appropriate height */
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: auto;
        max-height: 500px; /* Limit max height for large screens */
    }

    @media (max-width: 768px) {
    .hide-datalabels {
        display: none; /* Hide x-axis labels */
    }

    /* Table header styles */
    #salesReportTable th {
        background-color: #f2f2f2;
        color: black;
        text-decoration: none;
        cursor: default;
    }

    /* Prevent hover effects */
    #salesReportTable th:hover {
        background-color: #f2f2f2;
    }
}


    @media print {
        body * {
            visibility: hidden;
        }
        #salesChart, #salesChart * {
            visibility: visible;
        }
        #salesChart {
            position: absolute;
            top: 0;
            left: 0;
        }
    }

    /* Add this CSS to your styles */
    .fc-event-description {
        padding: 10px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin: 10px 0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .fc-list-event {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        margin-bottom: 10px;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .fc-list-event:hover {
        background-color: #f3f4f6;
    }

    .fc-toolbar {
        background-color: #f7f7f7;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

 /* Adjust font size for medium screens (tablets) */
    @media (max-width: 992px) {
        .fc-toolbar-title {
            font-size: 24px;
        }
    }

    /* Adjust font size for small screens (mobile) */
    @media (max-width: 767px) {
        .fc-toolbar-title {
            font-size: 18px;
            line-height: 1.2; /* Ensure good readability with smaller text */
        }
    }

    /* Additional safeguard for very small screens */
    @media (max-width: 480px) {
        .fc-toolbar-title {
            font-size: 14px;
        }
    }


</style>

<body>
    
    <?php require 'loader.php'; ?>
    
    
    <?php require 'templates/admin_header.php'; ?>

    <div class="flex">
        <?php require 'templates/admin_navbar.php'; ?>
        <main>
        <div class="wrapper">
        <div class="w-100">


            <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="subtitle mb-0">Transactions</h4>
        <div>
            <?php if ($currentUser && $currentUser->role === ROLE_ADMIN): ?>
                <button class="btn btn-secondary" id="toggleActions">
                    <i class="fa-solid fa-toggle-on"></i> Toggle Actions
                </button>
            <?php endif; ?>
            <button class="btn btn-primary" id="printSales">
                <i class="fa-solid fa-floppy-disk"></i> Print or Save to PDF
            </button>
            <a href="admin_sales2.php">
                <button class="btn btn-primary">Product Sales</button>
            </a>
        </div>
    </div>
    <hr />
    <div class="table-responsive">
        <table id="transactionsTable" class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>TX #</th>
                    <th>Date</th>
                    <th>Processed by</th>
                    <th>Role</th>
                    <th>Product #</th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Subtotal</th>
                    <?php if ($currentUser && $currentUser->role === ROLE_ADMIN || ROLE_MANAGER): ?>
                        <th class="action-column">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <?php
                            $originalSubtotal = $transaction->quantity * $transaction->price;
                            $subtotal = $transaction->discountPercentage > 0 
                                ? $originalSubtotal * (1 - $transaction->discountPercentage / 100) 
                                : $originalSubtotal;
                            $formattedDate = date('m/d/y h:iA', strtotime($transaction->created_at));
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction->transaction_number) ?></td>
                            <td><?= htmlspecialchars($formattedDate) ?></td>
                            <td><?= htmlspecialchars($transaction->seller_name) ?></td>
                            <td><?= htmlspecialchars($transaction->seller_role) ?></td>
                            <td><?= htmlspecialchars($transaction->product_code) ?></td>
                            <td><?= htmlspecialchars($transaction->product_name) ?></td>
                            <td><?= htmlspecialchars($transaction->quantity) ?></td>
                            <td>₱<?= htmlspecialchars(number_format($transaction->price, 2)) ?></td>
                            <td><?= htmlspecialchars(number_format($transaction->discountPercentage)) ?>%</td>
                            <td>₱<?= htmlspecialchars(number_format($subtotal, 2)) ?></td>
                            <?php if ($currentUser && $currentUser->role === ROLE_ADMIN || ROLE_MANAGER): ?>
                                <td class="action-column">
                                     <button class="btn btn-warning btn-sm return-item" 
                                            data-id="<?= htmlspecialchars($transaction->id) ?>" 
                                            data-product="<?= htmlspecialchars($transaction->product_code) ?>" 
                                            data-quantity="<?= htmlspecialchars($transaction->quantity) ?>">
                                        Void Order
                                    </button> 
                                    <button class="btn btn-danger btn-sm delete-transaction" 
                                            data-id="<?= htmlspecialchars($transaction->id) ?>">
                                        Delete
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="11" class="text-center">No transactions found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<hr/>
<br>



</div>



</div>


<!-- <script src="./js/loader.js"></script> -->

<script>
        // Example: Show loader on page load and hide it after content is loaded
        showLoader();

        window.addEventListener('load', () => {
            hideLoader();
        });
    </script>
    
<script>
    var dataTable = new simpleDatatables.DataTable("#transactionsTable", {
            searchable: true
    });

    var dataTable = new simpleDatatables.DataTable("#salesReportTable", {
            searchable: true,
            ordering: false
    });
    


</script>




<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Handle delete button click
        document.querySelectorAll('.delete-transaction').forEach(button => {
            button.addEventListener('click', (e) => {
                const transactionId = e.target.getAttribute('data-id');
                
                if (confirm('Are you sure you want to delete this transaction?')) {
                    fetch('api/delete_transaction.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: transactionId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Transaction deleted successfully.');
                            location.reload(); // Refresh the page to update the table
                        } else {
                            alert('Failed to delete transaction: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('An error occurred: ' + error.message);
                    });
                }
            });
        });
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Add click event listeners to all "Void Order" buttons (class return-item)
        document.querySelectorAll(".return-item").forEach(button => {
            button.addEventListener("click", () => { 
                // Get data attributes from the button
                const itemId = button.getAttribute("data-id");
                const productCode = button.getAttribute("data-product");
                const quantity = button.getAttribute("data-quantity");

                // Confirm the action (updated message for "void")
                if (confirm(`Are you sure you want to void the order for ${quantity} units of product ${productCode}?`)) {
                    // Make a POST request to the server
                    fetch('api/return_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'return_item', // Specify the action
                            item_id: itemId,      // Pass the order item ID
                            quantity: quantity    // Pass the void quantity (full)
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Handle the server response
                        if (data.success) {
                            alert('Order voided successfully.');
                            location.reload(); // Reload the page to update the table
                        } else {
                            alert(`Error: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred.');
                    });
                }
            });
        });
    });
</script>

</body>
</html>
