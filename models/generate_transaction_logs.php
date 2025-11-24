<?php
require '../templates/tcpdf/tcpdf.php'; // Load TCPDF library
require_once __DIR__.'/../_init.php';

// Get today's date
$today = date('Y-m-d');

// Get today's transaction logs
$transactionLogs = Order::getAllTransactionLogs();
$todayTransactionLogs = array_filter($transactionLogs, function($log) use ($today) {
    return date('Y-m-d', strtotime($log['date_created'])) == $today;
});

// Create a new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Today\'s Transaction Logs');
$pdf->SetSubject('Transaction Logs');
$pdf->SetKeywords('Transaction Logs');

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 25);

// Add title
$pdf->Cell(0, 15, 'Spicy Mama', 0, 1, 'C');

// Set font
$pdf->SetFont('helvetica', '', 20);

// Add title
$pdf->Cell(0, 10, 'Today\'s Transaction Logs', 0, 1, 'C');

// Set font
$pdf->SetFont('helvetica', 'B', 10);

// Add table header
$pdf->Cell(30, 10, 'Transaction #', 1, 0, 'C');
$pdf->Cell(50, 10, 'Payment', 1, 0, 'C');
$pdf->Cell(30, 10, 'Change', 1, 0, 'C');
$pdf->Cell(30, 10, 'Date', 1, 1, 'C');

// Add table rows
$pdf->SetFont('helvetica', '', 10);
foreach ($todayTransactionLogs as $log) {
    $pdf->Cell(30, 10, $log['transaction_number'], 1, 0, 'C');
    $pdf->Cell(50, 10, $log['payment_info'], 1, 0, 'C');
    $pdf->Cell(30, 10, $log['change_info'], 1, 0, 'C');
    $pdf->Cell(30, 10, date('m/d/Y', strtotime($log['date_created'])), 1, 1, 'C');
}

// Close and output PDF document
$pdf->Output('today_transaction_logs.pdf', 'I');