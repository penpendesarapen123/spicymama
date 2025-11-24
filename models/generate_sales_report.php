<?php
require '../templates/tcpdf/tcpdf.php'; // Load TCPDF library
require_once __DIR__.'/../_init.php';

// Fetch data
$todayTransactions = Sales::getTodayTransactions();
$monthlyTransactions = Sales::getMonthlyTransactions();

// Group transactions by transaction number
function groupTransactionsByNumber($transactions) {
    $grouped = [];
    foreach ($transactions as $transaction) {
        $transactionNumber = $transaction['transaction_number'];
        if (!isset($grouped[$transactionNumber])) {
            $grouped[$transactionNumber] = [];
        }
        $grouped[$transactionNumber][] = $transaction;
    }
    return $grouped;
}

$groupedTodayTransactions = groupTransactionsByNumber($todayTransactions);
$groupedMonthlyTransactions = groupTransactionsByNumber($monthlyTransactions);

// Create PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 25);

// Title
$pdf->Cell(0, 10, 'Spicy Mama Sales Report', 0, 1, 'C');


// Today's Sales Table
$pdf->Ln();
$pdf->SetFont('Helvetica', '', 20);
$pdf->Cell(0, 10, "Today's Transactions", 0, 1);
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(30, 8, 'Transac #', 1, 0, 'C', 1);
$pdf->Cell(50, 8, 'Date', 1, 0, 'C', 1);
$pdf->Cell(40, 8, 'Seller', 1, 0, 'C', 1);
$pdf->Cell(40, 8, 'Products (ID)', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Subtotal', 1, 1, 'C', 1);

$totalTodaySales = 0; // Track total of today's sales
foreach ($groupedTodayTransactions as $transactionNumber => $transactions) {
    $formattedDate = date('M d, Y h:i A', strtotime($transactions[0]['date']));
    $sellerName = $transactions[0]['seller_name'];
    $totalSubtotal = 0;

    // First row: Transaction number, date, and seller details
    $pdf->Cell(30, 8, $transactionNumber, 1);
    $pdf->Cell(50, 8, $formattedDate, 1);
    $pdf->Cell(40, 8, $sellerName, 1);

    // Merge cells for product IDs and subtotals
    $productsCell = '';
    $subtotalCell = '';
    foreach ($transactions as $transaction) {
        $productSubtotal = $transaction['price'] * $transaction['quantity'] * (1 - $transaction['discountPercentage'] / 100);
        $productsCell .= $transaction['product_code'] . "\n";
        $subtotalCell .= 'P' . number_format($productSubtotal, 2) . "\n";
        $totalSubtotal += $productSubtotal;
    }

    // Display products and subtotals
    $pdf->MultiCell(40, 8, trim($productsCell), 1, 'C', 0, 0);
    $pdf->MultiCell(30, 8, trim($subtotalCell), 1, 'R', 0, 1);

    // Add to overall today sales
    $totalTodaySales += $totalSubtotal;

    // Total subtotal row
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(160, 8, '', 0, 0); // Empty cells for alignment
    $pdf->Cell(30, 8, 'P' . number_format($totalSubtotal, 2), 1, 1, 'R');
    $pdf->SetFont('Helvetica', '', 12);
}

// Display total of all sold for today
$pdf->SetFont('Helvetica', 'B', 14  );
$pdf->Ln();
$pdf->Cell(160, 10, 'Total of All Sold Today:', 0, 0, 'R');
$pdf->Cell(30, 10, 'P' . number_format($totalTodaySales, 2), 1, 1, 'R');
$pdf->SetFont('Helvetica', '', 12);

// Monthly Transactions Table
$pdf->Ln();
$pdf->SetFont('Helvetica', '', 20);
$pdf->Cell(0, 10, 'Monthly Transactions', 0, 1);
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetFillColor(230, 230, 230);

$pdf->Cell(30, 8, 'Transac #', 1, 0, 'C', 1);
$pdf->Cell(50, 8, 'Date', 1, 0, 'C', 1);
$pdf->Cell(40, 8, 'Seller', 1, 0, 'C', 1);
$pdf->Cell(40, 8, 'Products (ID)', 1, 0, 'C', 1);
$pdf->Cell(30, 8, 'Subtotal', 1, 1, 'C', 1);

$totalMonthlySales = 0; // Track total of monthly sales
foreach ($groupedMonthlyTransactions as $transactionNumber => $transactions) {
    $formattedDate = date('M d, Y h:i A', strtotime($transactions[0]['date']));
    $sellerName = $transactions[0]['seller_name'];
    $totalSubtotal = 0;

    // First row: Transaction number, date, and seller details 
    $pdf->Cell(30, 8, $transactionNumber, 1);
    $pdf->Cell(50, 8, $formattedDate, 1);
    $pdf->Cell(40, 8, $sellerName, 1);

    // Merge cells for product IDs and subtotals
    $productsCell = '';
    $subtotalCell = '';
    foreach ($transactions as $transaction) {
        $productSubtotal = $transaction['price'] * $transaction['quantity'] * (1 - $transaction['discountPercentage'] / 100);
        $productsCell .= $transaction['product_code'] . "\n";
        $subtotalCell .= 'P' . number_format($productSubtotal, 2) . "\n";
        $totalSubtotal += $productSubtotal;
    }

    // Display products and subtotals
    $pdf->MultiCell(40, 8, trim($productsCell), 1, 'C', 0, 0);
    $pdf->MultiCell(30, 8, trim($subtotalCell), 1, 'R', 0, 1);

    // Add to overall monthly sales
    $totalMonthlySales += $totalSubtotal;

    // Total subtotal row
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(160, 8, '', 0, 0); // Empty cells for alignment
    $pdf->Cell(30, 8, 'P' . number_format($totalSubtotal, 2), 1, 1, 'R');
    $pdf->SetFont('Helvetica', '', 12);
}

// Display total of all sold for the month
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Ln();
$pdf->Cell(160, 10, 'Total of All Sold This Month:', 0, 0, 'R');
$pdf->Cell(30, 10, 'P' . number_format($totalMonthlySales, 2), 1, 1, 'R');
$pdf->SetFont('Helvetica', '', 12);

// Output PDF
$pdf->Output('sales_report.pdf', 'I'); // Inline display
?>
