<?php

require_once __DIR__.'/../_init.php';
// Assuming this is the get_sales_report.php file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $filter = $_POST['filter']; // daily, weekly, monthly, yearly

    if ($action === 'get_sales_report') {
        $salesReport = getSalesReport($filter);
        echo json_encode(['success' => true, 'salesReport' => $salesReport]);
    }
}

function getSalesReport($filter) {
    global $connection;
    $dateCondition = '';
    
    // Adjust the date condition based on the filter
    switch ($filter) {
        case 'daily':
            $dateCondition = "DATE(o.created_at) = CURDATE()";
            break;
        case 'weekly':
            $dateCondition = "YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'monthly':
            $dateCondition = "MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
            break;
        case 'yearly':
            $dateCondition = "YEAR(o.created_at) = YEAR(CURDATE())";
            break;
        default:
            // Default to daily
            $dateCondition = "DATE(o.created_at) = CURDATE()";
            break;
    }


    $stmt = $connection->prepare('

        SELECT 
            o.name AS cashier_name,
            o.role AS cashier_role,
            SUM(oi.quantity * oi.price * (1 - (o.discountPercentage / 100))) AS total_sales,
            COUNT(o.id) AS total_transactions
        FROM 
            orders o
        INNER JOIN 
            order_items oi ON o.id = oi.order_id
        WHERE ' . $dateCondition . '
        GROUP BY o.name, o.role
        ORDER BY 
            total_sales DESC;
    ');

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}