<?php
require_once '../_init.php';

if (isset($_GET['start']) && isset($_GET['end'])) {
    $startDate = $_GET['start'];
    $endDate = $_GET['end'];
    $salesData = [];

    global $connection;
    $sql_command = "
        SELECT 
            orders.transaction_number AS transactionNumber, 
            orders.created_at AS transactionDate, 
            products.name AS product, 
            products.product_code AS productCode, -- Include the product_code
            order_items.quantity AS quantity, 
            order_items.price AS price,
            orders.discountPercentage AS discountPercentage, -- Fetch discount percentage from orders table
            (order_items.quantity * order_items.price) AS totalPrice
        FROM 
            order_items
        INNER JOIN 
            orders ON order_items.order_id = orders.id
        INNER JOIN 
            products ON order_items.product_id = products.id
        WHERE 
            DATE(orders.created_at) BETWEEN :start_date AND :end_date
        ORDER BY 
            orders.created_at
    ";

    $stmt = $connection->prepare($sql_command);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    $salesDataForListView = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groupedData = [];
    foreach ($salesDataForListView as $row) {
        $date = date('Y-m-d', strtotime($row['transactionDate']));
        if (!isset($groupedData[$date])) {
            $groupedData[$date] = [];
        }
        $groupedData[$date][] = $row;
    }

    $events = [];
    foreach ($groupedData as $date => $transactions) {
        $transactionDetails = '';
        $subtotal = 0;
        $totalDiscounted = 0;
    
        foreach ($transactions as $transaction) {
            $totalPrice = $transaction['totalPrice'];
            $discountedPrice = $totalPrice - ($totalPrice * ($transaction['discountPercentage'] / 100));
            $totalDiscounted += $totalPrice * ($transaction['discountPercentage'] / 100);
            $subtotal += $discountedPrice;
            $startDate = date('Y-m-d 00:00:00', strtotime($_GET['start']));
            $endDate = date('Y-m-d 23:59:59', strtotime($_GET['end']));
    
            $transactionDetails .= "
                <b>Transaction #{$transaction['transactionNumber']}</b><br>
                Date/Time: {$transaction['transactionDate']}<br>
                Product: {$transaction['product']} (Code: {$transaction['productCode']})<br>
                Qty: {$transaction['quantity']} | Price: ₱" . number_format($transaction['price'], 2) . " | Total: ₱" . number_format($totalPrice, 2) . "<br>
                Discount: {$transaction['discountPercentage']}% | Discounted Total: ₱" . number_format($discountedPrice, 2) . "<br><br>";
        }
    
        $events[] = [
            'title' => '₱' . number_format($subtotal, 2),
            'start' => date('Y-m-d\TH:i:sP', strtotime($date . ' 00:00:00')),
            'allDay' => true,
            'description' => $transactionDetails,
            'amount' => $subtotal, // Include subtotal for frontend
            'backgroundColor' => getHeatmapColor($subtotal) // Assign heatmap color
        ];
    }
    
    echo json_encode($events);
}

function getHeatmapColor($salesAmount) {
    if ($salesAmount >= 1001) {
        return '#28A745'; // Green
    } elseif ($salesAmount >= 501) {
        return '#FFC300'; // Orange
    } else {
        return '#FF0000'; // Red
    }
}
?>
