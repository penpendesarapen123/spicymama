<?php
require '../templates/tcpdf/tcpdf.php'; // Load TCPDF library
require_once __DIR__.'/../_init.php';  // Initialize the application and database

if (isset($_POST['salesData']) && is_array($_POST['salesData'])) {
    $salesData = $_POST['salesData'];

    if (empty($salesData)) {
        echo 'No sales data provided.';
        exit;
    }

    try {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $html = '<h1>Sales Report</h1>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr>
            <th>Transaction Number</th>
            <th>Date</th>
            <th>Seller Name</th>
            <th>Role</th>
            <th>Product Code</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Discount</th>
        </tr>';

        foreach ($salesData as $sale) {
            $html .= '<tr>
                <td>' . htmlspecialchars($sale['transaction_number']) . '</td>
                <td>' . htmlspecialchars($sale['date']) . '</td>
                <td>' . htmlspecialchars($sale['seller_name']) . '</td>
                <td>' . htmlspecialchars($sale['seller_role']) . '</td>
                <td>' . htmlspecialchars($sale['product_code']) . '</td>
                <td>' . htmlspecialchars($sale['price']) . '</td>
                <td>' . htmlspecialchars($sale['quantity']) . '</td>
                <td>' . htmlspecialchars($sale['discountPercentage']) . '</td>
            </tr>';
        }

        $html .= '</table>';

        $pdf->writeHTML($html);
        $pdf->Output('sales_product_report.pdf', 'D');
    } catch (Exception $e) {
        echo 'Error generating PDF: ' . $e->getMessage();
    }
} else {
    echo 'Invalid or missing sales data.';
}