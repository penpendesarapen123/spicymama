<?php
// Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

$totalSales = Sales::getTotalSales();
$transactions = OrderItem::all();

$year = date('Y'); // Current year
$month = date('m'); // Current month
$monthlySales = Sales::getMonthlySales($year, $month);
$annualSales = Sales::getAnnualSales($year);

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
            <div class="sales-container">
                <div class="card sales-info-card monthly-sales-card">
                    <div class="card-header"><i class="fas fa-calendar-alt"></i> Monthly Sales</div>
                    <div class="card-body">₱ <?= isset($monthlySales) ? number_format($monthlySales, 2) : '0.00' ?></div>
                </div>
                <div class="card sales-info-card annual-sales-card">
                    <div class="card-header"><i class="fas fa-calendar"></i> Annual Sales</div>
                    <div class="card-body">₱ <?= isset($annualSales) ? number_format($annualSales, 2) : '0.00' ?></div>
                </div>
                <div class="card sales-info-card total-sales-card">
                    <div class="card-header"><i class="fas fa-chart-line"></i> Total Sales</div>
                    <div class="card-body">₱ <?= isset($totalSales) ? number_format($totalSales, 2) : '0.00' ?></div>
                </div>
            </div>

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
                    <?php if ($currentUser && $currentUser->role === ROLE_ADMIN): ?>
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
                            <?php if ($currentUser && $currentUser->role === ROLE_ADMIN): ?>
                                <td class="action-column">
                                    <!-- <button class="btn btn-warning btn-sm return-item" 
                                            data-id="<?= htmlspecialchars($transaction->id) ?>" 
                                            data-product="<?= htmlspecialchars($transaction->product_code) ?>" 
                                            data-quantity="<?= htmlspecialchars($transaction->quantity) ?>">
                                        Return Item
                                    </button> -->
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
<div class="table-responsive">
    <h4 class="subtitle">Cashier Sales Report</h4>
    <br>
    <div>
    <label for="dateFilter">Filter by:</label>
    <select id="dateFilter" name="dateFilter">
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
    </select>
    
    <button class="btn btn-primary btn-sm" id="filterReportBtn">Filter Report</button>
    <button class="btn btn-primary btn-sm" id="printReportBtn"><i class="fas fa-print"></i> Print Report</button>
</div>

<table id="salesReportTable" class="table table-striped">
    <thead class="table-dark">
        <tr>

            <th>Cashier Name</th>
            <th>Role</th>
            <th>Total Sales</th>
            <th>Total Transactions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Data will be populated here -->
    </tbody>
</table>

</div>



<hr/>

<div class="container mt-4">
    <h4 class="subtitle">Calendar Sale Chart</h4>
    <div class="row">
        <div class="col-md-8">
            <div id="calendar" heatmap="true"></div>
        </div>
        <div class="col-md-4">
            <div class="heatmap-legend">
                <h4>Sales Heatmap Legend</h4>
                <div class="legend-item">
                    <span class="color-box" style="background-color: #FF0000;"></span>
                    <span style="font-size: large">0 - 500 Sales</span>
                </div>
                <div class="legend-item">
                    <span class="color-box" style="background-color: #FFC300;"></span>
                    <span style="font-size: large">501 - 1000 Sales</span>
                </div>
                <div class="legend-item">
                    <span class="color-box" style="background-color: #28A745;"></span>
                    <span style="font-size: large">1001+ Sales</span>
                </div>
            </div>
        </div>
    </div>
</div>
    <hr />
    <br>
    <h4 class="subtitle">Sales Report</h4>
    <div class="date-picker-container">
        <div class="date-input-group">
            <label for="startDate" style="font-weight: bold;">Start Date:</label>
            <input type="text" id="startDate" class="form-control" placeholder="Select Start Date">
        </div>
        <div class="date-input-group">
            <label for="endDate" style="font-weight: bold;">End Date:</label>
            <input type="text" id="endDate" class="form-control" placeholder="Select End Date">
        </div>
        <div class="date-input-group">
            <label for="salesbutton" style="color: #f4f6f9;"> .</label>
            <button id="fetchSalesDataBtn" class="btn btn-primary">Show Sales Data</button>
        </div>
        <div class="date-input-group">
            <label for="printbutton" style="color: #f4f6f9;"> .</label>
            <button id="printChartBtn" class="btn btn-primary"><i class="fas fa-print"></i> Print Chart</button>
        </div>
    </div>
    <button class="btn btn-primary" onclick="showSales('hour')"><i class="fas fa-clock"></i> Hourly</button>
    <button class="btn btn-primary" onclick="showSales('week')"><i class="fas fa-calendar-week"></i> Weekly</button>
    <button class="btn btn-primary" onclick="showSales('month')"><i class="fas fa-calendar-alt"></i> Monthly</button>
    <button class="btn btn-primary" onclick="showSales('year')"><i class="fas fa-calendar"></i> Yearly</button>

    <div class="chart-container">
        <canvas id="salesChart" height="120"></canvas>
    </div>

    <!-- Description section for the chart -->
    <div id="chartDescription" class="chart-description" style="margin-top: 20px; background-color: #ddd; padding: 10px;"></div>

<div id="detailedViewModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Transaction Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Detailed view content will be dynamically added here -->
        <div id="modalDetailedViewContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
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
    
<script>
    var dataTable = new simpleDatatables.DataTable("#transactionsTable", {
            searchable: true
    });

    var dataTable = new simpleDatatables.DataTable("#salesReportTable", {
            searchable: true,
            ordering: false
    });
    
    $(function () {
        // Initialize date range picker using jQuery UI Datepicker
        $("#startDate, #endDate").datepicker({
            dateFormat: "yy-mm-dd"
        });

        // Trigger data fetch when the button is clicked
        $("#fetchSalesDataBtn").on("click", function () {
            const startDate = $("#startDate").val();
            const endDate = $("#endDate").val();

            if (startDate && endDate) {
                fetchSalesData(startDate, endDate);
            } else {
                alert("Please select both a start and end date.");
            }
        });
    });

    // Function to fetch sales data for a date range or timeframe
    function fetchSalesData(startDate, endDate, timeframe = null) {
        $.ajax({
            url: 'models/getsalesdata.php',
            type: 'GET',
            data: { start_date: startDate, end_date: endDate, timeframe: timeframe },
            success: function (response) {
                try {
                    const salesData = JSON.parse(response);
                    if (salesData && salesData.labels && salesData.sales) {
                        salesChart.data.labels = salesData.labels;
                        salesChart.data.datasets[0].data = salesData.sales;
                        salesChart.update();

                        updateChartDescription();
                    } else {
                        console.error("Invalid data format returned:", salesData);
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', response);
                }
            },
            error: function (error) {
                console.error('Error fetching sales data:', error);
            }
        });
    }

    // Function to handle timeframe-based sales data fetching
    function showSales(timeframe) {
        fetchSalesData(null, null, timeframe);
        
    }

    // Chart.js setup for sales data
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                borderColor: 'rgba(0, 123, 255, 1)',
                data: []
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    color: '#000', // Text color
                    align: 'top', // Position above data points
                    anchor: 'end', // Anchor the label to the point
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    formatter: function (value, context) {
                        // Format value with commas
                        return new Intl.NumberFormat('en-US').format(value);
                    }
                }
            },
            scales: {
                x: { display: true, title: { display: true, text: 'Time' } },
                y: { display: true, title: { display: true, text: 'Sales (PHP)' } }
            }
        },
        plugins: [ChartDataLabels] // Activate the plugin
    });

    // Function to toggle data labels visibility based on screen size
    function toggleDataLabelsVisibility(chart) {
        const shouldHideDataLabels = window.innerWidth < 768; // Hide data labels for small screens
        chart.options.plugins.datalabels.display = !shouldHideDataLabels; // Update data labels visibility
        chart.update(); // Apply changes
    }

    // Run on initial load
    toggleDataLabelsVisibility(salesChart);

    // Add resize event listener
    window.addEventListener('resize', function () {
        toggleDataLabelsVisibility(salesChart);
    });

    // Initially load sales data for the current month
    fetchSalesData(moment().startOf('month').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));

    updateChartDescription();
    

    // Function to generate a descriptive paragraph for the chart
    function generateChartDescription(labels, salesData) {
        if (labels.length === 0 || salesData.length === 0) {
            return "No sales data is currently available for the selected timeframe.";
        }

        const totalSales = salesData.reduce((acc, curr) => acc + curr, 0);
        const highestSale = Math.max(...salesData);
        const highestSaleIndex = salesData.indexOf(highestSale);
        const highestSaleLabel = labels[highestSaleIndex];
        const averageSales = (totalSales / salesData.length);
        const formattedAverageSales = new Intl.NumberFormat('en-US').format(averageSales.toFixed(2));
        return `
            The sales chart displays data from ${labels[0]} to ${labels[labels.length - 1]}. 
            The total sales during this period amounted to PHP ${totalSales.toLocaleString()}. 
            The highest sales occurred on ${highestSaleLabel}, with a total of PHP ${highestSale.toLocaleString()}. 
            On average, the sales were approximately PHP ${formattedAverageSales}.
        `;
    }



    // Function to update the chart description on the page
    function updateChartDescription() {
        const description = generateChartDescription(
            salesChart.data.labels,
            salesChart.data.datasets[0].data
        );
        document.getElementById('chartDescription').innerHTML = description;
    }
</script>

</script>

<script>

$(document).ready(function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today'
        },
        
        selectable: true,
        select: function (info) {
            // Adjust the end date to be inclusive
            var adjustedEndDate = new Date(info.end);
            adjustedEndDate.setDate(adjustedEndDate.getDate() - 1);

            // Custom date format: MM/DD/YYYY
            function formatDate(date) {
                const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-based
                const day = date.getDate().toString().padStart(2, '0');
                const year = date.getFullYear();
                return `${month}/${day}/${year}`;
            }

            // Fetch events within the selected range
            var eventsInRange = calendar.getEvents().filter(event => {
                var eventDate = event.start;
                return eventDate >= info.start && eventDate <= adjustedEndDate;
            });

            // Sort events by date
            eventsInRange.sort((a, b) => a.start - b.start);

            // Calculate the total amount
            var totalAmount = eventsInRange.reduce((sum, event) => {
                return sum + (event.extendedProps.amount || 0);
            }, 0);

            // Format total amount with commas
            function formatCurrency(amount) {
                return amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Determine header title (single date or range)
            var headerTitle = info.start.toDateString() === adjustedEndDate.toDateString()
                ? `Total Amount of Transactions on ${formatDate(info.start)}`
                : `Total Amount of Transactions from ${formatDate(info.start)} - ${formatDate(adjustedEndDate)}`;

            // Generate the modal content
            var modalContent = `
                <h5>${headerTitle}: ₱${formatCurrency(totalAmount)}</h5>
                <div>
                    ${eventsInRange.map(event => `
                        <div style="border: 1px solid #ccc; padding: 10px; margin: 5px;">
                            <strong>Date: ${formatDate(event.start)} | Total Amount: ₱${formatCurrency(event.extendedProps.amount || 0)}</strong><br>
                            ${event.extendedProps.description || ''}
                        </div>
                    `).join('')}
                </div>
            `;

            // Set the modal content
            $('#modalDetailedViewContent').html(modalContent);

            // Show the modal
            $('#detailedViewModal').modal('show');
        },
        events: function (fetchInfo, successCallback, failureCallback) {
            $.ajax({
                type: 'GET',
                url: 'models/getCalendarSalesData.php',
                data: {
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr
                },
                success: function (data) {
                    try {
                        var events = JSON.parse(data);
                        successCallback(events);
                    } catch (error) {
                        console.error("Error parsing events data:", error);
                        failureCallback();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("Failed to load events data:", textStatus, errorThrown);
                    failureCallback();
                }
            });
        },
        views: {
            listDay: {
                type: 'list',
                duration: { days: 1 },
                buttonText: 'today',
                eventContent: function (arg) {
                    // Simplify content in the listDay view
                    return {
                        html: `
                            <div style="border: 1px solid #ccc; padding: 10px; margin: 5px;">
                                <strong>Date: ${arg.event.start.toLocaleDateString()} | Total Amount: ₱${(arg.event.extendedProps.amount || 0).toFixed(2)}</strong><br>
                                ${arg.event.extendedProps.description || ''}
                            </div>
                        `
                    };
                }
            }
        }
    });

    calendar.render();
    $("<input type='text' id='calendarDatePicker' style='margin-left: 20px;' />").insertAfter(".fc-toolbar-title");
    $("#calendarDatePicker").datepicker({
        dateFormat: "yy-mm-dd",
        onSelect: function(dateText) {
            calendar.gotoDate(dateText);
        }
    });

});



document.getElementById('printChartBtn').addEventListener('click', function () {
    const canvas = document.getElementById('salesChart');

    // Ensure the chart is rendered before attempting to print
    if (!canvas) {
        console.error("Sales chart canvas not found.");
        return;
    }

    // Generate the chart description
    const description = generateChartDescription(
        salesChart.data.labels,
        salesChart.data.datasets[0].data
    );

    // Convert the canvas content to an image
    const imgData = canvas.toDataURL('image/png');

    // HTML content for the print window
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>POS Spicy Mama</title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                    text-align: center;
                }
                img {
                    max-width: 100%;
                    height: auto;
                }
                .description {
                    margin-top: 20px;
                    font-size: 14px;
                    text-align: left;
                }
            </style>
        </head> 
        <body>
            <h3>Sales Chart</h3>
            <img src="${imgData}" alt="Spicy Mama Sales Chart">
            <div class="description">
                <p>${description}</p>
            </div>
        </body>
        </html>
    `;

    // Create a Blob for the content
    const blob = new Blob([printContent], { type: 'text/html' });

    // Generate a URL for the Blob
    const url = URL.createObjectURL(blob);

    // Open the URL in a new window
    const printWindow = window.open(url);

    if (!printWindow) {
        console.error("Failed to open a new window for printing.");
        return;
    }

    // Add an event listener to clean up and close the print window
    printWindow.onload = function () {
        printWindow.focus();
        printWindow.print();

        // Close the print window after printing
        printWindow.onafterprint = function () {
            printWindow.close();
        };

        // Cleanup
        URL.revokeObjectURL(url);
    };
});

document.getElementById('printSales').addEventListener('click', (event) => {
    event.preventDefault(); // Prevent default behavior if it's part of a form or anchor
    window.open('models/generate_sales_report.php', '_blank'); // Opens in a new tab
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
    document.addEventListener('DOMContentLoaded', () => {
        const toggleActionsButton = document.getElementById('toggleActions');
        const actionColumns = document.querySelectorAll('.action-column');

        if (toggleActionsButton) {
            toggleActionsButton.addEventListener('click', () => {
                actionColumns.forEach(column => {
                    column.style.display = column.style.display === 'none' ? '' : 'none';
                });
                
                // Toggle the button icon or label for user feedback
                const icon = toggleActionsButton.querySelector('i');
                if (icon) {
                    if (icon.classList.contains('fa-toggle-on')) {
                        icon.classList.replace('fa-toggle-on', 'fa-toggle-off');
                    } else {
                        icon.classList.replace('fa-toggle-off', 'fa-toggle-on');
                    }
                }
            });
        }
    });

    document.getElementById("filterReportBtn").addEventListener("click", function() {
    let dateFilter = document.getElementById("dateFilter").value;
    
    // Send request to the server to fetch data based on the selected filter
    fetch('api/get_sales_report.php', {
        method: 'POST',
        body: new URLSearchParams({
            action: 'get_sales_report', 
            filter: dateFilter // daily, weekly, monthly, yearly
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateSalesReport(data.salesReport); // Call function to populate the table
        } else {
            alert('Error fetching report data');
        }
    })
    .catch(error => console.error('Error:', error));
});

function populateSalesReport(salesReport) {
    let tbody = document.getElementById("salesReportTable").getElementsByTagName('tbody')[0];
    tbody.innerHTML = ''; // Clear previous data
    
    salesReport.forEach(row => {
        let tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.cashier_name}</td>
            <td>${row.cashier_role}</td>
            <td>${'₱' + row.total_sales.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}</td>
            <td>${row.total_transactions.toLocaleString()}</td>
        `;
        tbody.appendChild(tr);
    });
}

document.getElementById("printReportBtn").addEventListener("click", function () {
    const dateFilter = document.getElementById("dateFilter").value; // Get the current filter value
    const currentDate = new Date();
    let timeframe = '';

    // Determine the timeframe based on the filter
    switch (dateFilter) {
        case 'daily':
            timeframe = `for ${currentDate.toLocaleDateString()}`; // Example: "for 12/7/2024"
            break;
        case 'weekly':
            const startOfWeek = new Date(currentDate.setDate(currentDate.getDate() - currentDate.getDay() + 1)); // Monday
            const endOfWeek = new Date(currentDate.setDate(startOfWeek.getDate() + 6)); // Sunday
            timeframe = `from ${startOfWeek.toLocaleDateString()} to ${endOfWeek.toLocaleDateString()}`; // Example: "from 12/4/2024 to 12/10/2024"
            break;
        case 'monthly':
            const month = currentDate.toLocaleString('default', { month: 'long' }); // Example: "December"
            const year = currentDate.getFullYear();
            timeframe = `for ${month} ${year}`; // Example: "for December 2024"
            break;
        case 'yearly':
            timeframe = `for the year ${currentDate.getFullYear()}`; // Example: "for the year 2024"
            break;
        default:
            timeframe = ''; // No filter selected
            break;
    }

    const printWindow = window.open('', '', 'height=600,width=800');
    const tableContent = document.getElementById("salesReportTable").outerHTML;

    printWindow.document.write(`
        <html>
            <head>
                <title>Cashier Sales Report</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    h2{
                        text-align: center;
                        margin-bottom: 0px;
                    }
                    h3{
                        text-align: center;
                    }
                    h4 {
                        text-align: center;
                        margin-bottom: 20px;
                        color: gray;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    th, td {
                        border: 1px solid black;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                        color: black;
                        text-decoration: none !important; /* Remove underline */
                        cursor: default; /* Prevent pointer cursor */
                    }
                    th:hover {
                        background-color: #f2f2f2; /* No hover effect */
                    }
                    a {
                        text-decoration: none; /* Prevent links in headers */
                        color: inherit; /* Match text color */
                    }
                    a:hover {
                        text-decoration: none; /* No hover effect for links */
                    }
                </style>
            </head>
            <body>
                <h2>POS Spicy Mama | Cashier Sales Report</h2>
                <h4>${timeframe}</h4>
                ${tableContent}
            </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
    printWindow.close();
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Add click event listeners to all "Return Item" buttons
        document.querySelectorAll(".return-item").forEach(button => {
            button.addEventListener("click", () => {
                // Get data attributes from the button
                const itemId = button.getAttribute("data-id");
                const productCode = button.getAttribute("data-product");
                const quantity = button.getAttribute("data-quantity");

                // Confirm the action
                if (confirm(`Are you sure you want to return ${quantity} of product ${productCode}?`)) {
                    // Make a POST request to the server
                    fetch('api/return_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'return_item', // Specify the action
                            item_id: itemId,      // Pass the order item ID
                            quantity: quantity    // Pass the return quantity
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Handle the server response
                        if (data.success) {
                            alert('Item returned successfully.');
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
