<?php
// Assuming you have already connected to the database
require_once '../_guards.php';
Guard::adminAndManagerOnly();

// Get the start and end dates from the request; default to today if not set
$startDate = $_GET['start_date'] ?? date('Y-m-d');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Fetch sales data between selected dates
$query = "SELECT DATE(created_at) as date, SUM(amount) as total_sales 
          FROM sales 
          WHERE DATE(created_at) BETWEEN :start_date AND :end_date 
          GROUP BY DATE(created_at)";
$stmt = $pdo->prepare($query);
$stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);

$salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the chart
$labels = array_column($salesData, 'date');  // Extract the dates for labels
$sales = array_column($salesData, 'total_sales'); // Extract the sales totals for data

// Return the data in JSON format
echo json_encode(['labels' => $labels, 'sales' => $sales]);
?>
