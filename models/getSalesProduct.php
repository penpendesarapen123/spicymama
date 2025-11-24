<?php
require_once __DIR__.'/../_init.php';
require_once '../templates/tcpdf/tcpdf.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['product_ids'])) {
    $productIds = json_decode($_GET['product_ids'], true);

    if (!empty($productIds)) {
        // Fetch today's sales data
        $todaySalesData = Product::getSalesByProductIds($productIds, 'today');
        $totalTodaySales = array_sum(array_column($todaySalesData, 'sales'));
        $totalTodaySalesAmount = array_sum(array_column($todaySalesData, 'total_sales_amount'));

        // Fetch this month's sales data
        $currentYear = date('Y');
        $currentMonth = date('m');
        $monthSalesData = Product::getSalesByProductIds($productIds, 'month', $currentYear, $currentMonth);
        $totalMonthSales = array_sum(array_column($monthSalesData, 'sales'));
        $totalMonthSalesAmount = array_sum(array_column($monthSalesData, 'total_sales_amount'));

        // Generate PDF using TCPDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // Header
        $html = '<h1>Sales Report</h1>';

        // Today's Sales Table
        $html .= '<h2>Today\'s Sales</h2>';
        $html .= '<table border="1" cellspacing="3" cellpadding="4">';
        $html .= '<tr><th>Product Code</th><th>Name</th><th>Quantity Sold</th><th>Total Sales Amount</th></tr>';
        foreach ($todaySalesData as $data) {
            $html .= '<tr>
                <td>' . htmlspecialchars($data['product_code']) . '</td>
                <td>' . htmlspecialchars($data['name']) . '</td>
                <td>' . htmlspecialchars($data['sales']) . '</td>
                <td>' . htmlspecialchars(number_format($data['total_sales_amount'], 2)) . '</td>
            </tr>';
        }
        $html .= '<tr>
            <td colspan="2"><strong>Total</strong></td>
            <td><strong>' . htmlspecialchars($totalTodaySales) . '</strong></td>
            <td><strong>' . htmlspecialchars(number_format($totalTodaySalesAmount, 2)) . '</strong></td>
        </tr>';
        $html .= '</table>';

        // Monthly Sales Table
        $html .= '<h2>Monthly Sales</h2>';
        $html .= '<table border="1" cellspacing="3" cellpadding="4">';
        $html .= '<tr><th>Product Code</th><th>Name</th><th>Quantity Sold</th><th>Total Sales Amount</th></tr>';
        foreach ($monthSalesData as $data) {
            $html .= '<tr>
                <td>' . htmlspecialchars($data['product_code']) . '</td>
                <td>' . htmlspecialchars($data['name']) . '</td>
                <td>' . htmlspecialchars($data['sales']) . '</td>
                <td>' . htmlspecialchars(number_format($data['total_sales_amount'], 2)) . '</td>
            </tr>';
        }
        $html .= '<tr>
            <td colspan="2"><strong>Total</strong></td>
            <td><strong>' . htmlspecialchars($totalMonthSales) . '</strong></td>
            <td><strong>' . htmlspecialchars(number_format($totalMonthSalesAmount, 2)) . '</strong></td>
        </tr>';
        $html .= '</table>';

        // Output the PDF
        $pdf->writeHTML($html);
        $pdf->Output('sales_products_report.pdf', 'I'); // Inline display in the browser
    } else {
        echo "No valid products selected!";
    }
} else {
    echo "No products selected!";
}
?>
