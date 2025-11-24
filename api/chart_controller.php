<?php
require_once __DIR__.'/../_init.php';

if ($_GET['action'] === 'stock_add_decrease') {
    try {
        global $connection;

        // Fetch added stock from inventory_log
        $stmtAdded = $connection->prepare(
            'SELECT DATE(updated_at) AS date, SUM(quantity_added) AS quantity
             FROM inventory_log
             GROUP BY DATE(updated_at)'
        );
        $stmtAdded->execute();
        $addedStock = $stmtAdded->fetchAll(PDO::FETCH_ASSOC);

        // Fetch decreased stock from orders (assuming 'orders' and 'order_items' tables exist)
        $servicesCategoryId = 24;  // Replace with the actual category ID for services
$stmtDecreased = $connection->prepare(
    'SELECT DATE(o.created_at) AS date, SUM(oi.quantity) AS quantity
     FROM orders o
     JOIN order_items oi ON o.id = oi.order_id
     WHERE oi.product_id NOT IN (SELECT id FROM products WHERE category_id = :services_category_id)  -- Exclude services
     GROUP BY DATE(o.created_at)'
);
$stmtDecreased->bindParam(':services_category_id', $servicesCategoryId, PDO::PARAM_INT);
$stmtDecreased->execute();
        $decreasedStock = $stmtDecreased->fetchAll(PDO::FETCH_ASSOC);

        // Fetch newly added products (New Items) from the products table
        $stmtNewItems = $connection->prepare(
            'SELECT DATE(created_at) AS date, COUNT(id) AS quantity
             FROM products
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)  -- Last 7 days, adjust if needed
             GROUP BY DATE(created_at)'
        );
        $stmtNewItems->execute();
        $newItems = $stmtNewItems->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'added' => $addedStock,
                'decreased' => $decreasedStock,
                'new_items' => $newItems,  // Newly added products
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
