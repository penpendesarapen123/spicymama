<?php
require_once __DIR__.'/../_init.php';
// Function to fetch top-selling products based on filters
function getTopSellingProducts($timeframe, $filterType) {
    global $connection;

    // Determine the date condition based on the selected timeframe
    $dateCondition = '';
    switch ($timeframe) {
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
            $dateCondition = "YEAR(o.created_at) = YEAR(CURDATE())";
    }

    // Determine the order by clause based on the filter type
    $orderBy = $filterType === 'quantity' ? 'quantity_sold DESC' : 'total_sales DESC';

    // Build the SQL query
    $sql = "
        SELECT 
            p.product_code, 
            p.name AS product_name, 
            SUM(oi.quantity) AS quantity_sold,
            SUM(oi.quantity * oi.price) AS total_sales
        FROM 
            order_items oi
        INNER JOIN 
            products p ON oi.product_id = p.id
        INNER JOIN 
            orders o ON oi.order_id = o.id
        WHERE 
            p.category_id != 24 AND
            $dateCondition
        GROUP BY 
            p.id
        ORDER BY 
            $orderBy
        LIMIT 5";

    // Prepare and execute the statement
    $stmt = $connection->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    // Get parameters from request
    $timeframe = $_GET['timeframe'] ?? 'daily';
    $filterType = $_GET['filter'] ?? 'quantity';
    
    // Fetch the data
    $data = getTopSellingProducts($timeframe, $filterType);
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

?>