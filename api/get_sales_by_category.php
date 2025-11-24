<?php
require_once __DIR__ . '/../_init.php';
require_once __DIR__ . '/../models/Category.php';

header('Content-Type: application/json');

try {
    // Get the timeframe from the request (default to 'daily')
    $timeframe = $_GET['timeframe'] ?? 'daily';

    // Fetch sales data based on the timeframe
    $salesData = Category::getSalesByCategory($timeframe);

    echo json_encode([
        'success' => true,
        'data' => $salesData,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
