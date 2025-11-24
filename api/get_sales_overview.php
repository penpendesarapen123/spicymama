<?php
require_once __DIR__ . '/../_init.php';
require_once __DIR__ . '/../models/Sales.php';
require_once __DIR__ . '/../models/OrderItem.php';

header('Content-Type: application/json');

$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'today';

try {
    switch ($timeframe) {
        case 'weekly':
            $totalSales = Sales::WeeklySales();
            $productsSold = Sales::getWeeklyProductsSold();
            $transactions = OrderItem::getWeeklyTransactions();
            break;
        case 'monthly':
            $totalSales = Sales::MonthlySales();
            $productsSold = Sales::getMonthlyProductsSold();
            $transactions = OrderItem::getMonthlyTransactions();
            break;
        case 'yearly':
            $totalSales = Sales::YearlySales();
            $productsSold = Sales::getYearlyProductsSold();
            $transactions = OrderItem::getYearlyTransactions();
            break;
        case 'today':
        default:
            $totalSales = Sales::TodaySales();
            $productsSold = Sales::getTodayProductsSold();
            $transactions = OrderItem::getTotalTransactionsToday();
            break;
    }

    echo json_encode([
        'success' => true,
        'totalSales' => $totalSales,
        'productsSold' => $productsSold,
        'transactions' => $transactions,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}