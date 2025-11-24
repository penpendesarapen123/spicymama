<?php
// Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

$todaySales = Sales::TodaySales();
$productsSold = Sales::getTodayProductsSold();
$transactions = OrderItem::getTotalTransactionsToday();
$accounts =  User::getTotalUsers();
$totalSuppliers = Supplier::getTotalSuppliers();
$categoriesData = Category::getProductsCountPerCategory();
$totalProducts = 0;
foreach ($categoriesData as $category) {
    $totalProducts += $category['product_count'];
}

$outOfStock = Product::countOutOfStock();
$runningLow = Product::countRunningLowStock();

// Retrieve the counts per stock level per category
$countsPerStockLevelPerCategory = Category::getStockLevelperCategory();

// Separate the "Other" category
$otherCategory = null;
$sortedCategories = [];

// Iterate through the data to separate "Other"
foreach ($countsPerStockLevelPerCategory as $row) {
    if ($row['category_id'] == 23) {
        $otherCategory = $row; // Save "Other" category
    } else {
        $sortedCategories[] = $row; // Add all other categories
    }
}

// Sort the remaining categories alphabetically by `category_name`
usort($sortedCategories, function ($a, $b) {
    return strcasecmp($a['category_name'], $b['category_name']); // Case-insensitive comparison
});

// Append "Other" category to the end
if ($otherCategory) {
    $sortedCategories[] = $otherCategory;
}

// Initialize arrays to store the counts for each stock level
$outOfStockCounts = [];
$runningLowCounts = [];
$fullyStockedCounts = [];
$overStockedCounts = [];
$categories = [];

// Iterate through the retrieved data
foreach ($sortedCategories as $row) {
    $categories[] = $row['category_name'];
    $outOfStockCounts[] = $row['out_of_stock_count'];
    $runningLowCounts[] = $row['running_low_count'];
    $fullyStockedCounts[] = $row['fully_stocked_count'];
    $overStockedCounts[] = $row['overstock_count'];
}

// Construct the data for the bar graph
$chartData = [
    'labels' => $categories,
    'datasets' => [
        [
            'label' => 'Out of Stock',
            'data' => $outOfStockCounts,
            'backgroundColor' => 'rgba(255, 0, 0, 1)'
        ],
        [
            'label' => 'Running Low',
            'data' => $runningLowCounts,
            'backgroundColor' => 'rgba(255, 204, 0, 1)'
        ],
        [
            'label' => 'Fully Stocked',
            'data' => $fullyStockedCounts,
            'backgroundColor' => 'rgba(0, 128, 0, 1)' // Adjusted alpha for transparency
        ],
        [
            'label' => 'Over Stocked',
            'data' => $overStockedCounts,
            'backgroundColor' => 'rgba(70, 130, 180, 1)'
        ]
    ]
];

// Encode the chart data into JSON format
$chartDataJSON = json_encode($chartData);

$topSellingProducts = OrderItem::getTopSellingProductsPareto();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Dashboard</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="./css/loader.css">

     <!-- Datatables Library -->
     <link rel="stylesheet" type="text/css" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<style>
   
    body {
        overflow: hidden;
        background-color: #f9f9f9;
        color: #333;
        margin: 0;
    }
   
    .dashboard {
        display: grid;
        grid-template-columns: 3fr 1fr 1fr; /* Adjusts size based on content */
        gap: 20px; /* Reduces unnecessary space */
        justify-items: stretch; /* Ensures all items stretch to fill available space */
    }

    .dashboard1 {
        display: grid;
        grid-template-columns: 1fr 1fr;/* Adjusts size based on content */
        padding-top:20px;
        gap: 20px; /* Reduces unnecessary space */
        justify-items: stretch; /* Ensures all items stretch to fill available space */
    }

    .section:hover, .account:hover, .supplier:hover {
        transform: translateY(-5px);
    }

    .section {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%; /* Ensures it fills the grid cell */
    }

    .section1 {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%; /* Ensures it fills the grid cell */
    }

    @media (max-width: 992px) {
        .dashboard, .dashboard1 {
            grid-template-columns: 1fr;
        }

        .row {
            flex-direction: column;
        }
    }


    .subtitle {
            font-size: 1.4rem;
            font-weight: bold;
    }

    .section-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 20px; /* Add gap between cards */
    }

    .card {
        padding: 20px;
        flex: 1; /* Make it flexible */
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 100%;
        flex-direction: column;
    }

    .card i {
        font-size: 50px;
    }

    .card-header {
        font-weight: bold;
        margin-bottom: 10px;
        font-weight: 20px
    }

    .card-content {
        font-size: 28px;
        font-weight: bold;
    }

    .card-container {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }

    .chart-container canvas {
    width: 100% !important; /* Allow the chart to fully expand horizontally */
    height: 100% !important; /* Allow the chart to fully expand vertically */
    max-height: 500px; /* Optional: Cap to avoid excessive growth on large screens */
    max-width: 100%; /* Optional: Keep proportional scaling */
}

    .pie-chart-container canvas {
        position: relative;
        width: 50%; /* Adjust the width as needed */
        max-width: 400px; /* Add a maximum width */
        margin: auto; /* Center the chart */
        height: 300px; /* Adjust the height */
    }

    .chart-container, .table-container {
        height: 100%; /* Make both the chart and table containers fill their section height */
        position: relative;
    }

    .card-content table {
        width: 100%;
        height: 100%;
        border-collapse: collapse;
        font-size: 16px;
        border-radius: 5px;
    }

    .card-content th {
        background-color: #f0f0f0;
        font-weight: bold;
        padding: 10px;
        text-align: left;
    }

    .card-content td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    .card-content tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .card-content tr:hover {
        background-color: #f1f1f1;
    }
    .section a, .section1 a{
        text-decoration: none;
        color: black
    }

    .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    }

    .filter-container {
        display: flex;
        align-items: center;
        gap: 5px; /* Add some spacing between the label and dropdown */
    }

    .sales-filter {
        padding: 5px;
        font-size: 1rem;
    }

    .sales {
        background-color: #b1121dff;
        color: white;
    }

    .account {
        background-color:  rgba(30, 144, 255, 1);
        color: white;
    }

    .outstock{
        background-color: rgba(139, 0, 0, 1);
        color: white;
    }

    .lowstock{
        background-color: rgba(255, 165, 0, 1);
        color: white;
    }

    .card-content i {
        margin-right: 5px; /* Adjust the spacing between the icon and content */
        color: white; /* Adjust the icon color */
        font-size: 20px; /* Adjust the icon size */
        transform: scale(1.5); /* Adjust the scale factor as needed */
        transform-origin: center; /* Ensure the icon scales from the center */
    }

    .supplier {
        background-color: rgba(255, 140, 0, 1); /* Orange color */
        color: white;
    }

    .supplier .card-content i {
        color: white; /* Icon color */
        font-size: 20px; /* Icon size */
        position: absolute;
        bottom: 25px;
        right: 20px;
    }

    .row-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        gap:10px;
    }

    .vertical-stack {
        display: flex;
        flex-direction: column;
        gap: 10px; /* Spacing between the stacked sections */
    }

    /* Modal styling */
    .modal {
        display: none; /* Hidden by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease; /* Smooth fade-in animation */
    }

    .modal-content {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 10px;
        width: 550px;
        max-width: 100%;
        text-align: left;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.3s ease; /* Smooth slide-in animation */
    }

    .modal-content h2 {
        margin-top: 0;
        font-size: 22px;
        color: #333;
    }

    /* Checkbox group styling */
    .checkbox-group {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Two columns */
        gap: 10px;
        margin-bottom: 20px;
    }

    /* Button group styling */
    .button-group {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    #topSellingChart {
    min-height: 300px;
    max-height: 300px;
}

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<body>
    
    <?php require 'loader.php'; ?>
    
    
    <?php require 'templates/admin_header.php' ?>
    <div class="flex">
        <?php require 'templates/admin_navbar.php' ?>
        <main>
            <div class="wrapper w-60p">
                <div class="flex justify-between items-center mb-0" style="justify-content: space-between;">
                    <span class="subtitle">Dashboard</span>
                    <button class="btn btn-md btn-primary" onclick="openPrintModal()"><i class="fa fa-print"></i> Print</button>
                </div>
                <hr/>

                <div class="modal" id="printModal">
                    <div class="modal-content" onclick="event.stopPropagation()">
                        <h2>Select Sections to Print</h2>
                        <br>
                        <form id="printForm" action="print.php" method="get">
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="sections[]" value="totalSales"> Sales Overview</label>
                                <label><input type="checkbox" name="sections[]" value="productsSold"> Products Sold</label>
                                <label><input type="checkbox" name="sections[]" value="transactions"> Transactions</label>
                                <label><input type="checkbox" name="sections[]" value="accounts"> Accounts</label>
                                <label><input type="checkbox" name="sections[]" value="suppliers"> Suppliers</label>
                                <label><input type="checkbox" name="sections[]" value="inventoryStatus"> Inventory Status</label>
                                <label><input type="checkbox" name="sections[]" value="productsCategory"> Products per Category</label>
                                <label><input type="checkbox" name="sections[]" value="topSellingProducts" onclick="toggleTopSellingFilters(this.checked)">
                                    Top Selling Products 
                                </label>
                                <label><input type="checkbox" name="sections[]" value="inventoryCategory"> Inventory Levels per Category</label>
                                <label><input type="checkbox" name="sections[]" value="salesCategory"> Sales by Category</label>
                                <label><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"> Select All</label>
                            </div>

                            <div id="topSellingFilters" style="display: none;">
                                <input type="hidden" id="printTimeframe" name="timeframe" value="daily">
                                <input type="hidden" id="printFilter" name="filter" value="quantity">
                                <!-- Hidden inputs for chart data -->
                                <input type="hidden" id="chartLabels" name="chartLabels" value="">
                                <input type="hidden" id="chartData" name="chartData" value="">
                            </div>

                            <input type="hidden" id="salesFilterValue" name="salesFilter" value="today">
                            <input type="hidden" id="salesByCategoryTimeframeInput" name="salesByCategoryTimeframe" value="daily">

                            <div class="button-group">
                                <button class="btn btn-primary" type="submit">Print</button>
                                <button class="btn btn-danger" type="button" onclick="closePrintModal()">Cancel</button>
                            </div>
                        </form> 
                    </div>
                </div>


                <div class="dashboard">
                    <!-- Section: Today's Sales -->
                    <div class="section">
                        <div class="section-header">
                            <div class="section-title">Sales Overview</div>
                            <div class="filter-container">
                                <label for="salesFilter">Filter by:</label>
                                <select id="salesFilter" class="sales-filter">
                                    <option value="today" selected>Today's Sales</option>
                                    <option value="weekly">Weekly Sales</option>
                                    <option value="monthly">Monthly Sales</option>
                                    <option value="yearly">Yearly Sales</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="card sales">
                                <div class="card-header">Total Sales</div>
                                <div class="card-content" id="totalSales">₱ 0.00</div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px">
                            <div class="card sales">
                                <div class="card-header">Products Sold</div>
                                <div class="card-content" id="productsSold">0</div>
                            </div>
                            <div class="card sales">
                                <div class="card-header">Transactions</div>
                                <div class="card-content" id="transactions">0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Account and Supplier-->
                    <div class="section1">
                        <div class="section-title">Accounts and Suppliers</div>
                        <a href="admin_home.php">
                        <div class="row">
                            <div class="card account" style="margin-bottom: 10px">
                                <div class="card-header">Active Accounts</div>
                                <div class="card-content" style="postion: relative"><?= number_format($accounts) ?></div>
                            </div>
                        </div>
                        </a>
                        <a href="admin_suppliers.php">
                        <div class="row">
                            <div class="card supplier">
                                <div class="card-header">Active Suppliers</div>
                                <div class="card-content" style="postion: relative"><?= number_format($totalSuppliers) ?></div>
                            </div>
                        </div>
                        </a>
                    </div>

                    <!-- Section: Inventory Status-->
                    <div class="section">
                    <a href="admin_home.php">
                        <div class="section-title">Inventory Status</div>
                        <div class="row">
                            <div class="card outstock" style="margin-bottom: 10px">
                                <div class="card-header">Out of Stock</div>
                                <div class="card-content"><?php echo number_format($outOfStock) ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="card lowstock">
                                <div class="card-header">Running Low</div>
                                <div class="card-content"><?php echo number_format($runningLow); ?></div>
                            </div>
                        </div>
                    </a>
                    </div>
                </div>

            <div class="dashboard1" >
                <div class="section" style="flex: 1 0 calc(50% - 10px); height: 100%;">
                <div class="section-header">
                    <div class="section-title">Top Selling Products</div>
                        <div class="filter-container">
                            <label for="timeframeSelect">Timeframe:</label>
                            <select id="timeframeSelect" class="sales-filter">
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="filter-container">
                            <label for="filterType">Filter by:</label>
                            <select id="filterType" class="sales-filter">
                                <option value="quantity" selected>Quantity Sold</option>
                                <option value="sales">Total Sales</option>
                            </select>
                        </div>
                    </div>
                        <div class="row">
                        <div class="chart-container" style="height: 100%;">
                            <canvas id="topSellingChart"></canvas>
                            <div id="chartDescription"></div>
                        </div>
                    </div>
                </div>

                <div class="section" style="flex: 1 0 calc(1% - 20px);">
                <div class="section-header">
                    <div class="section-title">Sales by Category</div>
                        <div class="filter-container">
                            <label for="salesByCategoryTimeframe">Filter by:</label>
                            <select id="salesByCategoryTimeframe" class="sales-filter">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="chart-container" style="height: 100%">
                            <canvas id="salesByCategoryChart"></canvas>
                        </div>
                            <div class="chart-description" id="salesByCategoryDescription"></div>
                    </div>
                </div>


                <div class="section" style="flex: 1 0 calc(50% - 10px); height: 100%">
                    <div class="section-title">Products Per Category</div>
                    <div class="row">
                        <div class="pie-chart-container">
                            <canvas id="pieChart"></canvas> <!-- Canvas for pie chart -->
                        </div>
                        <div class="text-center" id="totalProducts">Total Products: <?= number_format($totalProducts) ?></div>
                        <div class="chart-description" id="pieChartDescription"></div> 
                    </div>
                </div>
                

                <!-- Left: Inventory Levels -->
                <div class="section" style="flex: 1 0 calc(1% - 20px);">
                    <div class="section-title">Inventory Levels per Category</div>
                    <div class="row">
                        <div class="chart-container" style="height: 100%">
                            <!-- Bar graph for inventory levels -->
                            <canvas id="inventoryChart"></canvas>
                        </div>
                        <div id="inventoryChartDescription" class="chart-description"></div>
                    </div>
                </div>
                </div>
            </div>
            <?php require 'templates/admin_footer.php' ?>
        </main>
    </div>
</body>
</html>

<script src="./js/loader.js"></script>

<script>
    // Convert PHP array to JavaScript array
    var categoriesData = <?php echo json_encode($categoriesData); ?>;
</script>

<script>
// Sync the selected sales filter with the hidden input for printing
document.getElementById('salesFilter').addEventListener('change', function () {
    const timeframe = this.value;

    // Update the hidden input in the print form
    document.getElementById('salesFilterValue').value = timeframe;

    // Fetch data based on the selected filter
    fetch(`api/get_sales_overview.php?timeframe=${timeframe}`)
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Update the content dynamically
                document.getElementById('totalSales').textContent = `₱ ${parseFloat(data.totalSales).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                document.getElementById('productsSold').textContent = data.productsSold.toLocaleString();
                document.getElementById('transactions').textContent = data.transactions.toLocaleString();
            } else {
                console.error('Error fetching sales overview:', data.error);
            }
        })
        .catch((error) => console.error('Error:', error));
});

// Trigger the filter change event on page load to load today's data by default
document.getElementById('salesFilter').dispatchEvent(new Event('change'));

</script>

<script>
    
    var ctx = document.getElementById('pieChart').getContext('2d');

    var labels = categoriesData.map(category => category.category_name);
    var counts = categoriesData.map(category => category.product_count);

    var categoriesData = <?php echo json_encode($categoriesData); ?>;
    var totalProducts = <?php echo $totalProducts; ?>;

    var pieChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            data: counts,
            backgroundColor: [
                '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            datalabels: {
                color: '#fff',
                formatter: (value, ctx) => {
                    let percentage = ((value / totalProducts) * 100).toFixed(2);
                    return `${ctx.chart.data.labels[ctx.dataIndex]}: ${percentage}%`;
                }
            }
        },
        legend: {
            position: 'top'
        }
    }
});
    // Display the total number of products
    document.getElementById('totalProducts').innerText = 'Total Products: ' + totalProducts;

    function generatePieChartDescription() {
        const descriptionContainer = document.getElementById('pieChartDescription');
        let descriptionHTML= ` `;
        descriptionHTML += '<ul>';
        categoriesData.forEach(category => {
            const percentage = ((category.product_count / totalProducts) * 100).toFixed(2);
            descriptionHTML += `<li><strong>${category.category_name}:</strong> ${category.product_count.toLocaleString()} products (${percentage}%)</li>`;
        });
        descriptionHTML += '</ul>';
        descriptionContainer.innerHTML = descriptionHTML;
    }

    // Call the function to generate the pie chart description
    generatePieChartDescription();
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const salesByCategoryCanvas = document.getElementById('salesByCategoryChart');
    const salesByCategoryDescription = document.getElementById('salesByCategoryDescription');
    const salesByCategoryTimeframeSelect = document.getElementById('salesByCategoryTimeframe');
    let salesByCategoryChart;

    function fetchSalesByCategoryData(timeframe = 'daily') {
        fetch(`api/get_sales_by_category.php?timeframe=${timeframe}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const salesData = data.data;
                    const labels = salesData.map(item => item.category_name);
                    const sales = salesData.map(item => parseFloat(item.total_sales || 0));

                    if (salesByCategoryChart) {
                        salesByCategoryChart.data.labels = labels;
                        salesByCategoryChart.data.datasets[0].data = sales;
                        salesByCategoryChart.update();
                    } else {
                        salesByCategoryChart = new Chart(salesByCategoryCanvas.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Total Sales',
                                    data: sales,
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Category'
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Sales (in currency)'
                                        }
                                    }
                                }
                            }
                        });
                    }

                    updateSalesByCategoryDescription(salesData, timeframe);
                } else {
                    console.error('Error fetching sales data:', data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function updateSalesByCategoryDescription(data, timeframe) {
        let totalSales = data.reduce((sum, item) => sum + parseFloat(item.total_sales || 0), 0);
        let descriptionHTML = `<p>This chart shows the total sales per category for the selected timeframe (${timeframe}):</p>`;
        descriptionHTML += '<ul>';
        data.forEach(item => {
            descriptionHTML += `<li><strong>${item.category_name}:</strong> ₱${parseFloat(item.total_sales).toLocaleString()}</li>`;
        });
        descriptionHTML += `</ul><p><strong>Total Sales: ₱${totalSales.toLocaleString()}</strong></p>`;
        salesByCategoryDescription.innerHTML = descriptionHTML;
    }

    salesByCategoryTimeframeSelect.addEventListener('change', function () {
        fetchSalesByCategoryData(this.value);
    });

    fetchSalesByCategoryData();

});

document.getElementById('salesByCategoryTimeframe').addEventListener('change', function () {
    document.getElementById('salesByCategoryTimeframeInput').value = this.value;
});

 // Generate inventory chart description
function generateInventoryChartDescription(chartData) {
    if (!chartData || !chartData.labels || !chartData.datasets) {
        console.error("Chart data is not properly defined.");
        return "<p>Unable to generate a description. Chart data is incomplete.</p>";
    }

    const categories = chartData.labels;
    const datasets = chartData.datasets;
    let description = '<ul>';

    categories.forEach((category, index) => {
        let categoryDescription = '';
        datasets.forEach((dataset) => {
            const value = dataset.data[index];
            if (value > 0) {
                // Include only non-zero inventory levels
                if (!categoryDescription) {
                    categoryDescription += `<li><strong>${category}</strong>: `;
                }
                categoryDescription += `${dataset.label}: ${value} | `;
            }
        });

        if (categoryDescription) {
            // Remove trailing comma and close the list item
            categoryDescription = categoryDescription.slice(0, -2) + '</li>';
            description += categoryDescription;
        }
    });

    description += '</ul>';
    return description;
}

// Update the inventory chart description
function updateInventoryChartDescription(inventoryChart) {
    const descriptionContainer = document.getElementById('inventoryChartDescription');
    const description = generateInventoryChartDescription(inventoryChart.data);
    descriptionContainer.innerHTML = description;
}

// Render the chart and its description
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    const chartData = <?php echo $chartDataJSON; ?>;

    const inventoryChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                x: { stacked: false },
                y: { stacked: false }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });

    // Ensure the description is updated after the chart is fully initialized
    inventoryChart.update(); // This ensures the chart data is properly loaded
    updateInventoryChartDescription(inventoryChart); // Pass the chart instance to generate the description
});
    // Handle window resize
    window.addEventListener('resize', () => {
    pieChart.resize();
});
    
</script>
<script>
    function openPrintModal() {
        document.getElementById('printModal').style.display = 'flex';
    }

    // Close modal when clicking outside the content
    window.onclick = function(event) {
        const modal = document.getElementById("printModal");
        if (event.target === modal) {
            closePrintModal();
        }
    };

    function closePrintModal() {
        document.getElementById('printModal').style.display = 'none';
    }

    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById("selectAll");
        const checkboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }

    const checkboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const selectAllCheckbox = document.getElementById("selectAll");
            if (!checkbox.checked) {
                selectAllCheckbox.checked = false;
            }
        });
    });

    // Function to show or hide Top Selling Products filters
    function toggleTopSellingFilters(isChecked) {
        const filtersDiv = document.getElementById("topSellingFilters");
        filtersDiv.style.display = isChecked ? "block" : "none";
    }

    // Function to update the hidden form values
    function updatePrintForm() {
        document.getElementById("printTimeframe").value = timeframe;
        document.getElementById("printFilter").value = filterType;
    }

    // Event listeners for filter changes
    document.getElementById("timeframeSelect").addEventListener("change", function() {
        timeframe = this.value;
        updatePrintForm();
    });

    document.getElementById("filterType").addEventListener("change", function() {
        filterType = this.value;
        updatePrintForm();
    });

    // Initial setup to ensure the form has the correct values
    updatePrintForm();

</script>

<script>
// Initial data placeholders
var topSellingData = <?php echo json_encode($topSellingProducts); ?>;
var timeframe = "daily";
var filterType = "quantity";

// Chart reference
var topSellingChart;

// Function to update the chart
// Function to update the chart and print data
function updateTopSellingChart() {
    const labels = topSellingData.map(product => product.product_code);
    const data = filterType === "quantity"
        ? topSellingData.map(product => product.quantity_sold)
        : topSellingData.map(product => product.total_sales);

    const chartType = filterType === "quantity" ? "Quantity Sold" : "Total Sales";
    const unit = filterType === "quantity" ? "units" : "₱";

    // Format description
    const descriptionContainer = document.getElementById("chartDescription");
    let descriptionHTML = `<p>Top selling products (${chartType}) for the selected timeframe (${timeframe}):</p><ul>`;
    topSellingData.forEach(product => {
        const formattedSales = `₱${Number(product.total_sales).toLocaleString()}`;
        const formattedQuantity = `${product.quantity_sold}`;
        if (filterType === "quantity") {
            descriptionHTML += `<li><strong>${product.product_name}:</strong> ${formattedQuantity} sold | Total Sales: ${formattedSales}</li>`;
        } else {
            descriptionHTML += `<li><strong>${product.product_name}:</strong> ${formattedSales} | Units Sold: ${formattedQuantity}</li>`;
        }
    });
    descriptionHTML += "</ul>";
    descriptionContainer.innerHTML = descriptionHTML;

    // Update chart
    if (topSellingChart) {
        topSellingChart.destroy();
    }
    const ctx = document.getElementById("topSellingChart").getContext("2d");
    topSellingChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: chartType,
                data: data,
                backgroundColor: "rgba(75, 192, 192, 0.7)",
                borderColor: "rgba(75, 192, 192, 1)",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: "Products"
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: filterType === "quantity" ? "Quantity Sold" : "Total Sales (₱)"
                    },
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const product = topSellingData[context.dataIndex];
                            if (filterType === "quantity") {
                                return `${product.product_name}: ₱${Number(product.total_sales).toLocaleString()}`;
                            } else {
                                return `${product.product_name}: ${product.quantity_sold} units`;
                            }
                        }
                    }
                }
            }
        }
    });

    // Update hidden inputs for chart data in the print form
    document.getElementById("chartLabels").value = JSON.stringify(labels);
    document.getElementById("chartData").value = JSON.stringify(data);
}

// Function to fetch data based on filters
function fetchTopSellingData() {
    console.log("Fetching top selling data...");
    // Use AJAX or fetch API to call your PHP backend with the selected filters
    fetch(`api/get_top_selling.php?timeframe=${timeframe}&filter=${filterType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                topSellingData = data.data;
                updateTopSellingChart();
            } else {
                console.error("Error fetching data:", data.error);
            }
        })
        .catch(error => console.error("Fetch error:", error));
}

// Event listeners for filter changes
document.getElementById("timeframeSelect").addEventListener("change", function() {
    timeframe = this.value;
    fetchTopSellingData();
});

document.getElementById("filterType").addEventListener("change", function() {
    filterType = this.value;
    fetchTopSellingData();
});

// Initial chart load
fetchTopSellingData();

</script>


<script>
        // Example: Show loader on page load and hide it after content is loaded
        showLoader();

        window.addEventListener('load', () => {
            hideLoader();
        });
    </script>
