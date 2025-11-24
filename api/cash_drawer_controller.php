<?php

require_once __DIR__.'/../_init.php';

// Handle different actions based on the request.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'get_balance':
            // Allow only cashiers, managers, and admins to view the balance.
            Guard::allRoles();

            $balance = CashDrawer::getBalance();
            echo json_encode(['success' => true, 'balance' => $balance]);
            break;

        case 'update_balance':
            // Only admins can update the cash drawer balance.
            Guard::allRoles();

            $newBalance = $_POST['new_balance'];

            if (CashDrawer::updateBalance($newBalance)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cash drawer.']);
            }
            break;

        case 'set_balance':
            // Only admins can set the balance to any arbitrary value
            Guard::adminOnly();
    
            $newBalance = $_POST['new_balance'];
    
            if (CashDrawer::updateBalance($newBalance)) {
                echo json_encode(['success' => true, 'message' => 'Balance set successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to set cash drawer balance.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            break;
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
}
