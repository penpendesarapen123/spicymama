<?php
require_once __DIR__ . '/../_init.php';
require_once __DIR__ . '/../models/OrderItem.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the incoming JSON data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate the action
    if (isset($input['action']) && $input['action'] === 'return_item') {
        // Extract input data
        $orderItemId = $input['item_id'] ?? null;
        $quantity = $input['quantity'] ?? null;

        // Validate the input
        if (!is_numeric($orderItemId) || !is_numeric($quantity) || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
            exit;
        }

        // Call the returnItem method (no extra require needed)
        $response = OrderItem::returnItem($orderItemId, $quantity);
        
        // Return the response as JSON
        echo json_encode($response);
        exit;
    }

    // Handle invalid or missing actions
    echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    exit;
}
?>