<?php
// Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();


$selectedSections = isset($_GET['sections']) ? $_GET['sections'] : [];

$todaySales = Sales::TodaySales();
$productsSold = Sales::getTodayProductsSold();
$accounts =  User::getTotalUsers();
$totalSuppliers = Supplier::getTotalSuppliers();
$transactions = OrderItem::getTotalTransactionsToday();
$categoriesData = Category::getProductsCountPerCategory();
$totalProducts = 0;
foreach ($categoriesData as $category) {
    $totalProducts += $category['product_count'];
}
$topSellingProducts = OrderItem::getTopSellingProductsPareto();
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

$selectedSections = $_GET['sections'] ?? [];

// Initialize filters
$timeframe = $_GET['timeframe'] ?? 'daily';
$filter = $_GET['filter'] ?? 'quantity';

$chartLabels = isset($_GET['chartLabels']) ? json_decode($_GET['chartLabels'], true) : [];
$chartData = isset($_GET['chartData']) ? json_decode($_GET['chartData'], true) : [];

$salesFilter = isset($_GET['salesFilter']) ? $_GET['salesFilter'] : 'today';

// Fetch data from the API
$apiUrl = "http://localhost/posilisiklista/api/get_sales_overview.php?timeframe=" . $salesFilter; // BAGUHIN YUNG LINK
$salesOverview = json_decode(file_get_contents($apiUrl), true);

if ($salesOverview['success']) {
    $totalSales = $salesOverview['totalSales'];
    $productsSold = $salesOverview['productsSold'];
    $transactions = $salesOverview['transactions'];
} else {
    // Handle errors (e.g., fallback to default values)
    $totalSales = 0;
    $productsSold = 0;
    $transactions = 0;
}

$salesTimeframe = $_GET['salesByCategoryTimeframe'] ?? 'daily'; // Default to 'daily' if not set
$apiUrlSales = "https://localhost/posilisiklista/api/get_sales_by_category.php?timeframe=" . $salesTimeframe; //BAGUHIN YUNG LINK

// Use cURL to fetch data
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrlSales);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

$response = curl_exec($ch);

if ($response === false) {
    die("Error: Unable to fetch data from the API. " . curl_error($ch));
}

curl_close($ch);

$salesData = json_decode($response, true);

if (!$salesData || !isset($salesData['success']) || !$salesData['success']) {
    die("Error: Invalid response from API or no data available.");
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Dashboard</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="print.css" media="print">
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

<style>
.dashboard {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between; /* Adjust spacing */
    gap: 20px; /* Add gap between sections */
}

.section {
    flex: 1 0 30%;
    margin-bottom: 30px;
    margin-left: 10px;
    padding: 10px;
}

.section-title {
    font-size: 20px;
    margin-bottom: 10px;
    font-weight: bold;
    text-align:center;
}

.row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 20px; /* Add gap between cards */
}

.card {
    flex: 1 0 calc(33% - 20px); /* Adjust the width */
    background-color: #f0f0f0;
    border-radius: 5px;
    padding: 20px; /* Increase padding */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-header {
    font-weight: bold;
    margin-bottom: 10px;
    font-weight: 20px
}

.card-content {
    font-size: 18px;
    display: flex; /* Use flexbox to align items */
    justify-content: space-between; /* Align items with space between */
}

.chart-container {
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    visibility: visible !important;

}

.chart-container canvas {
    max-width: 100%; /* Ensure the canvas does not exceed the container width */
    justify-content: center;
    align-items: center;
}

.table-container {
    overflow-x: auto; /* Add horizontal scrollbar if needed */
}

.card-content table {
    width: 100%; /* Set table width to 100% of container */
    border-collapse: collapse; /* Collapse table borders */
    font-size: 18px
}

.card-content th,
.card-content td {
    padding: 8px; /* Add padding to table cells */
    border: 1px solid #ddd; /* Add border to table cells */
    text-align: left; /* Align text to the left */
    font-size: 18px;
}

.card-content th {
    background-color: #f2f2f2; /* Set background color for table header */
}

.section a{
    text-decoration: none;
    color: black
}

.sales {
    background-color: rgba(0, 128, 0, 1);
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
#topSellingChartPrint {
    
}

select{
    display: none;
}


    </style>

<body onafterprint="myFunction()" onload="window.print()">
<br>
                <section>
                    <h2 style="text-align: center; margin-bottom: 20px">Spicy Mama Dashboard</h2>
                    <div class="row">
                    <?php if (in_array('totalSales', $selectedSections)) : ?>
                        <div class="card">
                            <div class="card-header">Total Sales (<?= ucfirst($salesFilter) ?>)</div>
                            <div class="card-content">₱ <?= number_format($totalSales, 2) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array('productsSold', $selectedSections)) : ?>
                        <div class="card">
                            <div class="card-header">Products Sold (<?= ucfirst($salesFilter) ?>)</div>
                            <div class="card-content"><?= number_format($productsSold) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array('transactions', $selectedSections)) : ?>
                        <div class="card">
                            <div class="card-header">Transactions (<?= ucfirst($salesFilter) ?>)</div>
                            <div class="card-content"><?= number_format($transactions) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array('accounts', $selectedSections)) : ?>
                        <div class="card">
                            <div class="card-header">Total Accounts</div>
                            <div class="card-content"><?= $accounts ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array('suppliers', $selectedSections)) : ?>
                        <div class="card">
                            <div class="card-header">Total Suppliers</div>
                            <div class="card-content"><?= $totalSuppliers ?></div>
                        </div>
                    <?php endif; ?>

               <?php if (in_array('inventoryStatus', $selectedSections)) : ?>
                    <div class="section-title">Inventory Status</div>
                    <div class="card">
                        <div class="card-header">Out of Stock</div>
                        <div class="card-content"><?= $outOfStock ?></div>
                    </div>
                    <div class="card">
                        <div class="card-header">Running Low</div>
                        <div class="card-content"><?= $runningLow; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (in_array('topSellingProducts', $selectedSections)): ?>
                        <!-- Render chart for printing -->
                    <div class="section-title">Top Selling Products <?php echo htmlspecialchars($timeframe); ?> by <?php echo htmlspecialchars($filter === 'quantity' ? 'Quantity Sold' : 'Total Sales'); ?>
                    <canvas id="topSellingChartPrint" style="min-height: 300px; max-height: 300px; min-width: 300px; max-width: 700px;"></canvas>
                    </div>
                        <ul>
                        <?php foreach ($topSellingProducts as $product): ?>
                            <li>
                                <?php echo htmlspecialchars($product['product_name']); ?>: 
                                <strong>
                                <?php if ($filter === 'quantity'): ?>
                                    <?php echo htmlspecialchars($product['quantity_sold']); ?> units sold |
                                    Total Sales: ₱<?php echo number_format($product['total_sales'], 2); ?>
                                <?php else: ?>
                                    ₱<?php echo number_format($product['total_sales'], 2); ?>, 
                                    Units Sold: <?php echo htmlspecialchars($product['quantity_sold']); ?>
                                <?php endif; ?>
                                </strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (in_array('salesCategory', $selectedSections)) : ?>
                    
                    <div class="section-title">Sales by Category (<?= ucfirst($salesTimeframe) ?>)
                    <div class="row">
                        <div class="chart-container" style="height: 300px; max-width: 700px;">
                            <canvas id="salesByCategoryChart"></canvas>
                        </div>
                    </div>
                    </div>
                    <div class="chart-description">
                        <p>This chart shows the total sales by category for the selected timeframe (<?= ucfirst($salesTimeframe) ?>):</p>
                        <ul>
                            <?php
                            foreach ($salesData['data'] as $item) {
                                echo "<li><strong>{$item['category_name']}:</strong> ₱" . number_format($item['total_sales'], 2) . "</li>";
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (in_array('productsCategory', $selectedSections)) : ?>
                    <div class="section" style="flex: 1 0 calc(50% - 10px); height: 100%;">
                        <div class="section-title">Products Per Category
                        <div class="row">
                            <div class="pie-chart-container" style="height: 300px; width: 400px; margin: 0 auto;">
                                <canvas id="pieChart"></canvas> <!-- Canvas for pie chart -->
                            </div>
                        </div>
                        </div>
                            <div class="text-center" id="totalProducts">Total Products: <?= number_format($totalProducts) ?></div>
                            <div class="chart-description" id="pieChartDescription"></div> 
                        </div>
                        
                <?php endif; ?>

                <br>
                <?php if (in_array('inventoryCategory', $selectedSections)) : ?>
                    <div class="section-title">Inventory Levels per Category
                    <div class="row">
                        <div class="chart-container" style="height: 300px; max-width: 700px;">
                            <!-- Bar graph for inventory levels -->
                            <canvas id="inventoryChart"></canvas>
                        </div>
                    </div>
                    </div>
                        <div id="inventoryChartDescription" class="chart-description"></div>
                <?php endif; ?>

                       
                    <!-- Add more conditional checks for each section as needed -->
</body>
</html>
<script>
        const categoriesData = <?php echo json_encode($categoriesData); ?>;
        const totalProducts = <?php echo $totalProducts; ?>;
        const pieLabels = categoriesData.map(category => category.category_name);
        const pieCounts = categoriesData.map(category => category.product_count);

        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieCounts,
                    backgroundColor: [
                        '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

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


<script>
    // Convert PHP array to JavaScript array
    var categoriesData = <?php echo json_encode($categoriesData); ?>;
</script>


<script>
            const printLabels = <?php echo json_encode($chartLabels); ?>;
            const printData = <?php echo json_encode($chartData); ?>;
            
            new Chart(document.getElementById('topSellingChartPrint'), {
                type: 'bar',
                data: {
                    labels: printLabels,
                    datasets: [{
                        label: 'Top Selling Products',
                        data: printData,
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
                                text: 'Sales (₱)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>


<!-- JavaScript for Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function () {
        const salesData = <?php echo json_encode($salesData['data']); ?>; // Use actual API response
        const labels = salesData.map(item => item.category_name);
        const data = salesData.map(item => parseFloat(item.total_sales));

        const ctx = document.getElementById('salesByCategoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Sales',
                    data: data,
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
    });
    </script>
<script>
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
</script>

<script>
        window.onload = function() {
            setTimeout(function() {
                window.print(); // This will print the page after a delay
            }, 1200); // Change 5000 to the delay time you desire in milliseconds (here, it's 5 seconds)
        };
</script>

<script type="text/javascript">
   
 
   function myFunction(){
     window.location = "admin_dashboard.php"
    }
   // window.onafterprint  = myFunction() 
   
</script>