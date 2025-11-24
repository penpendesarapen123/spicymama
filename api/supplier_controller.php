<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../_init.php';
// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = $_POST['supplier_name'];
            $contact = $_POST['phone_number'];
            $address = $_POST['address'];

            if (!preg_match('/^[0-9]{10,15}$/', $contact)) {
                flashMessage('phone_error', 'Invalid phone number format. Enter 10 to 15 digits.', 'danger');
                header('Location: ../admin_suppliers.php');
                exit();
            }

            Supplier::create($name, $contact, $address);
            flashMessage('supplier_added', 'Supplier added successfully!', 'success');
            break;

        case 'edit':
            $supplierId = $_POST['supplier_id'];
            $name = $_POST['supplier_name'];
            $contact = $_POST['phone_number'];
            $address = $_POST['address'];

            if (!preg_match('/^[0-9]{10,15}$/', $contact)) {
                flashMessage('phone_error', 'Invalid phone number format. Enter 10 to 15 digits.', 'danger');
                header('Location: ../admin_suppliers.php');
                exit();
            }

            Supplier::update($supplierId, $name, $contact, $address);
            flashMessage('supplier_updated', 'Supplier updated successfully!', 'success');
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit();
    }

    // Redirect back to suppliers page
    header('Location: ../admin_suppliers.php');
    exit();
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'delete':
            $supplierId = $_GET['id'] ?? null;
            if ($supplierId) {
                Supplier::delete($supplierId);
                flashMessage('supplier_deleted', 'Supplier deleted successfully!', 'warning');
            }
            break;

        case 'restore':
            $supplierId = $_GET['id'] ?? null;
            $supplier = Supplier::findById($supplierId);

            if ($supplier && $supplier->deleted_at) {
                $supplier->restore();
                flashMessage('supplier_restored', 'Supplier restored successfully!', 'success');
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit();
    }

    // Redirect back to suppliers page
    header('Location: ../admin_suppliers.php');
    exit();
}
