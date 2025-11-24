<?php

date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../_init.php';

// Get the authenticated user (cashier) from the User class
$user = User::getAuthenticatedUser();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'proccess_order') {
    // Retrieve cashier information from the authenticated user
    $cashierName = $user->name ?? 'Unknown Cashier';
    $cashierRole = $user->role ?? 'Cashier';

    // Get discount percentage if applied
    $appliedDiscountPercentage = (float)post('discountPercentage') ?: 0;


    $payment = (float)post('payment') ?: 0;
    $change = (float)post('change') ?: 0;
    // Create the order with cashier information and discount percentage
    $order = Order::create($cashierName, $cashierRole, $appliedDiscountPercentage, 
    $payment, $change);

    

    // Fetch the transaction number for display
    $transactionNumber = $order->transaction_number;
    // Process order items
    $orderItems = [];
    if (empty($_POST['cart_item']) || !is_array($_POST['cart_item'])) {
        echo json_encode(['success' => false, 'error' => 'Cart items are missing or invalid.']);
        exit;
    }

    foreach ($_POST['cart_item'] as $item) {
        $product = Product::find($item['id']);
        OrderItem::add($order->id, $item);

        $size = $item['size'] ?? 'N/A';

        $orderItems[] = [
            'name' => $product->name,
            'size' => $size,
            'qty' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $item['quantity'] * $item['price'],
        ];
    }

    // Calculate total amount before discount
    $totalAmount = array_reduce($orderItems, function ($acc, $item) {
        return $acc + $item['subtotal'];
    }, 0);

    // Apply discount percentage to calculate the discount amount
    $discountAmount = $totalAmount * ($appliedDiscountPercentage / 100);
    $totalAmountAfterDiscount = $totalAmount - $discountAmount;

    // Calculate change
    $payment = (float)post('payment');
    $change = $payment - $totalAmountAfterDiscount;
   
    

    // Generate receipt HTML with cashier information and transaction number
    ob_start();
    ?>
    <div>
        <p>--------------------</p>
        <h1>Spicy Mama</h1>
        <p>Transaction Number: <?= htmlspecialchars($transactionNumber) ?></p> 
        <p>Date: <?= date('M-d-y h:i A') ?></p>
        <p><strong>Cashier:</strong> <?= htmlspecialchars($cashierName) ?></p>
        <table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Size</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['size']) ?></td>
                <td><?= htmlspecialchars($item['qty']) ?></td>
                <td>₱<?= number_format($item['price'], 2) ?></td>
                <td>₱<?= number_format($item['subtotal'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><b>Total(Sum)</b></td>
            <td><b>₱<?= number_format($totalAmount, 2) ?></b></td>
        </tr>
        <?php if ($appliedDiscountPercentage > 0): ?>
            <tr>
                <td colspan="4"><b>Discount (<?= $appliedDiscountPercentage ?>%)</b></td>
                <td><b>-₱<?= number_format($discountAmount, 2) ?></b></td>
            </tr>
            <tr>
                <td colspan="4"><b>Total(%)</b></td>
                <td><b>₱<?= number_format($totalAmountAfterDiscount, 2) ?></b></td>
            </tr>
        <?php endif; ?>
    </tfoot>
</table>

        <p>Payment: ₱<?= number_format($payment, 2) ?></p>
        <p>Change: ₱<?= number_format($change, 2) ?></p>
        <p>--------------------</p>
        <p>Thank you for your purchase!</p>
        <p>--------------------</p>
    </div>
    <?php
    $receiptHTML = ob_get_clean();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'receipt' => $receiptHTML]);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode(['received_data' => $_POST]);
    exit;
}
?>
