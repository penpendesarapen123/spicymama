<?php
require '../templates/tcpdf/tcpdf.php'; // Load TCPDF library
require_once __DIR__.'/../_init.php';  // Initialize the application and database

/**
 * Helper function to determine stock status based on quantity and category.
 */
function getStockStatus($quantity, $category) {
    if (in_array(strtolower($category->name), ['bike', 'bicycle'])) {
        if ($quantity == 0) {
            return 'Out of Stock';
        } elseif ($quantity <= 3) {
            return 'Running Low';
        } elseif ($quantity <= 5) {
            return 'Full Stock';
        } else {
            return 'Overstock';
        }
    } else {
        if ($quantity == 0) {
            return 'Out of Stock';
        } elseif ($quantity <= 5) {
            return 'Running Low';
        } elseif ($quantity <= 10) {
            return 'Full Stock';
        } else {
            return 'Overstock';
        }
    }
}

/**
 * Generate Inventory Report PDF.
 *
 * @param array $products Array of Product objects
 */
function generateInventoryReport($products, $newlyAddedProducts, $stockLogs) {
    // Create new PDF document with landscape orientation
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Company');
    $pdf->SetTitle('Spicy Mama Inventory Report');
    $pdf->SetSubject('Inventory Report');
    $pdf->SetKeywords('TCPDF, PDF, inventory, report');

    // Disable header/footer temporarily
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Add the main inventory page
    $pdf->AddPage();

    // Set title font and write the title
    $pdf->SetFont('helvetica', 'B', 30); // Bold, size 30
    $pdf->Write(0, 'Spicy Mama Inventory Report', '', 0, 'C', true, 0, false, false, 0); // Center alignment

    // Add a blank line after the title
    $pdf->Ln(10);

    $totalProducts = count($products);
    $stockStatusCounts = ['Out of Stock' => 0, 'Running Low' => 0, 'Full Stock' => 0, 'Overstock' => 0];
    $productsPerCategory = [];

    foreach ($products as $product) {
        $categoryName = $product->category ? $product->category->name : 'Uncategorized';
        $stockStatus = getStockStatus($product->quantity, $product->category);

        // Count products per category
        if (!isset($productsPerCategory[$categoryName])) {
            $productsPerCategory[$categoryName] = 0;
        }
        $productsPerCategory[$categoryName]++;

        // Count stock statuses
        if (isset($stockStatusCounts[$stockStatus])) {
            $stockStatusCounts[$stockStatus]++;
        }
    }

    // Display totals and stock statuses
    $pdf->SetFont('helvetica', '', 15);

    // Total Products
    $pdf->Write(0, "Total Products: $totalProducts", '', 0, 'L', true, 0, false, false, 0);
    $pdf->Ln(2);

    // Products Per Category
    $pdf->Write(0, "Products Per Category:", '', 0, 'L', true, 0, false, false, 0);
    foreach ($productsPerCategory as $categoryName => $count) {
        $pdf->Write(0, " - $categoryName: $count", '', 0, 'L', true, 0, false, false, 0);
    }
    $pdf->Ln(2);

    // Stock Status Breakdown
    $pdf->Write(0, "Stock Status Breakdown:", '', 0, 'L', true, 0, false, false, 0);
    foreach ($stockStatusCounts as $status => $count) {
        $pdf->Write(0, " - $status: $count", '', 0, 'L', true, 0, false, false, 0);
    }
    $pdf->Ln(10);

    // Group products by category
    $categories = [];
    foreach ($products as $product) {
        $categoryName = $product->category ? $product->category->name : 'Uncategorized';
        if (!isset($categories[$categoryName])) {
            $categories[$categoryName] = [];
        }
        $categories[$categoryName][] = $product;
    }


    // Loop through each category and create a section in the PDF
    foreach ($categories as $categoryName => $categoryProducts) {
        // Add category title
        $pdf->SetFont('helvetica', 'B', 25); // Bold, size 25
        $pdf->Write(0, $categoryName, '', 0, 'L', true, 0, false, false, 0); // Left alignment

        // Add a blank line after the category title
        $pdf->Ln(5);

        // Set font for the table content
        $pdf->SetFont('helvetica', '', 12); // Normal, size 12

        // Add table header for the category
        $table = '<table border="1" cellpadding="4"><thead><tr style="background-color:#f2f2f2;">';

        if (strtolower($categoryName) === 'services') {
            // Columns for "Services" category
            $table .= '
                <th>Service Code</th>
                <th>Service Name</th>
                <th>Price</th>
            ';
        } else {
            // Columns for other categories
            $table .= '
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Supplier</th>
                <th>Stock Status</th>
            ';
        }

        $table .= '</tr></thead><tbody>';

        // Add rows for the products in this category
        foreach ($categoryProducts as $product) {
            if (strtolower($categoryName) === 'services') {
                // Rows for "Services" category
                $table .= '
                    <tr>
                        <td>' . htmlspecialchars($product->product_code) . '</td>
                        <td>' . htmlspecialchars($product->name) . '</td>
                        <td>' . number_format($product->price, 2) . '</td>
                    </tr>
                ';
            } else {
                // Rows for other categories
                $stockStatus = getStockStatus($product->quantity, $product->category);
                $supplierName = $product->supplier ? $product->supplier->supplier_name : 'N/A';

                $table .= '
                    <tr>
                        <td>' . htmlspecialchars($product->product_code) . '</td>
                        <td>' . htmlspecialchars($product->name) . '</td>
                        <td>' . htmlspecialchars($product->quantity) . '</td>
                        <td>' . number_format($product->price, 2) . '</td>
                        <td>' . htmlspecialchars($supplierName) . '</td>
                        <td>' . $stockStatus . '</td>
                    </tr>
                ';
            }
        }

        $table .= '</tbody></table>';

        // Write the table for this category to the PDF
        $pdf->writeHTML($table, true, false, false, false, '');

        // Add a blank line after the table
        $pdf->Ln(10);
    }

    // Add a new page for "Newly Added Products"
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 25); // Bold, size 25
    $pdf->Write(0, 'Newly Added Products', '', 0, 'L', true, 0, false, false, 0); // Left alignment
    $pdf->Ln(5);

    // Add table for newly added products
    $pdf->SetFont('helvetica', '', 12); // Normal, size 12
    $newlyAddedTable = '<table border="1" cellpadding="4">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th>Product Code</th>
                <th>Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($newlyAddedProducts as $product) {
        $newlyAddedTable .= '
            <tr>
                <td>' . htmlspecialchars($product['product_code']) . '</td>
                <td>' . htmlspecialchars($product['name']) . '</td>
                <td>' . htmlspecialchars($product['quantity']) . '</td>
                <td>' . number_format($product['price'], 2) . '</td>
                <td>' . htmlspecialchars($product['created_at']) . '</td>
            </tr>
        ';
    }
    $newlyAddedTable .= '</tbody></table>';
    $pdf->writeHTML($newlyAddedTable, true, false, false, false, '');

    // Add a new page for "Added Stocks Logs"
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 25); // Bold, size 25
    $pdf->Write(0, 'Added Stocks Logs', '', 0, 'L', true, 0, false, false, 0); // Left alignment
    $pdf->Ln(5);

    // Add table for stock logs
    // Add table for stock logs
$pdf->SetFont('helvetica', '', 12); // Normal, size 12
$stockLogsTable = '<table border="1" cellpadding="4">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th>Product</th>
            <th>Qty Added</th>
            <th>Supplier Name</th>
            <th>Received By</th>
            <th>Date</th>
            <th>Logged By</th>
        </tr>
    </thead>
    <tbody>';

foreach ($stockLogs as $log) {
    $productName = htmlspecialchars($log['product_name'] ?? 'N/A');
    $quantityAdded = htmlspecialchars($log['quantity_added'] ?? 'N/A');
    $supplierName = htmlspecialchars($log['supplier_name'] ?? 'N/A');
    $receivedBy = htmlspecialchars($log['received_by'] ?? '-');
    $dateUpdated = htmlspecialchars($log['updated_at'] ?? 'N/A');
    $loggedBy = htmlspecialchars($log['updated_by'] ?? '-');

    $stockLogsTable .= '
        <tr>
            <td>' . $productName . '</td>
            <td>' . $quantityAdded . '</td>
            <td>' . $supplierName . '</td>
            <td>' . $receivedBy . '</td>
            <td>' . $dateUpdated . '</td>
            <td>' . $loggedBy . '</td>
        </tr>';
}

$stockLogsTable .= '</tbody></table>';
$pdf->writeHTML($stockLogsTable, true, false, false, false, '');

    // Output the PDF
    $pdf->Output('inventory_report.pdf', 'I');
}





// Fetch all products
$products = Product::all(); // Fetch all active products
$newlyAddedProducts = Product::getNewlyAddedItems();
$stockLogs = Product::inventoryLogs();
// Generate the report
generateInventoryReport($products, $newlyAddedProducts, $stockLogs);
