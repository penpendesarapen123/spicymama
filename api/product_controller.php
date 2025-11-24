<?php
require_once __DIR__.'/../_init.php';

function handleFileUpload($fileKey, $uploadDir) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $uploadFile = $uploadDir . basename($_FILES[$fileKey]['name']);
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadFile)) {
            return 'images/' . basename($_FILES[$fileKey]['name']);
        } else {
            error_log("Failed to move uploaded file.");
        }
    }
    return null;
}

if (get('action') === 'add') {
    $name = post('name');
    $category_id = post('category_id');
    $quantity = post('quantity');
    $price = post('price');
    $size_option = post('size_option');
    $custom_size = post('custom_size');
    $supplier_id = post('supplier_id');
    $product_code = post('product_code');
    $image = null;
    

    $size = $size_option === 'Custom' ? $custom_size : $size_option;

    if (empty($name) || empty($category_id) || empty($quantity) || empty($price) || empty($size) || empty($supplier_id)) {
        flashMessage('add_product', 'All fields are required.', 'danger');
        redirect('../admin_add_item.php');
    }

    if ($supplier_id === 'NULL') {
        $supplier_id = null; // Or leave it as an empty string, depending on your database setup
    }

    $image = handleFileUpload('image', __DIR__ . '/../images/');

    // Check if the product code is unique
    if (!Product::isProductCodeUnique($product_code)) {
        flashMessage('add_product', 'Product code already existing', 'danger');
        redirect('../admin_add_item.php');
    }

    try {
        Product::add($name, $category_id, $quantity, $price, $size, $image, $supplier_id, $product_code);
        flashMessage('add_product', 'Product added successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        error_log("Error adding product: " . $ex->getMessage());
        flashMessage('add_product', 'An error occurred', 'danger');
    }
    redirect('../admin_add_item.php');
}

if (get('action') === 'delete') {
    $id = get('id');
    try {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            flashMessage('delete_product', 'Product deleted successfully.', FLASH_SUCCESS);
        } else {
            flashMessage('delete_product', 'Product not found.', 'danger');
        }
    } catch (Exception $ex) {
        error_log("Error deleting product: " . $ex->getMessage());
        flashMessage('delete_product', 'An error occurred', 'danger');
    }
    redirect('../admin_home.php');
}

if (isset($_GET['action']) && $_GET['action'] === 'update') {
    $productId = $_GET['id'];
    $product = Product::find($productId);

    if ($product) {
        $product->name = $_POST['name'];
        $product->category_id = $_POST['category_id'];
        $product->price = $_POST['price'];
        $product->supplier_id = $_POST['supplier_id'] === "NULL" ? null : $_POST['supplier_id'];
        $product->product_code = $_POST['product_code'];
        
        // Handle size, including custom size
        $sizeOption = $_POST['size_option']; // Use the dropdown field name here
        $customSize = $_POST['custom_size'] ?? null; // Custom size input field

        // Determine the final size
        $product->size = ($sizeOption === 'Custom' && !empty($customSize)) ? $customSize : $sizeOption;

        // Handle image upload if a new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../images/';
            $uploadFile = $uploadDir . basename($_FILES['image']['name']);
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $product->image = 'images/' . basename($_FILES['image']['name']);
            }
        }

        if (!Product::isProductCodeUnique($product-> $productId)) {
            flashMessage('update_product', 'The product code already existing.', 'danger');
            header('Location: ../admin_update_item.php?id=' . $productId);
            exit;
        }

        try {
            $updateResult = $product->update(); // Call the updated method
        
            if ($updateResult) {
                flashMessage('update_product', 'Product updated successfully', FLASH_SUCCESS);
            } else {
                flashMessage('update_product', 'No changes were made to the product.', FLASH_WARNING);
            }
        } catch (Exception $ex) {
            error_log("Error updating product: " . $ex->getMessage());
            flashMessage('update_product', 'An error occurred while updating the product.', FLASH_ERROR);
        }
        
        
    } else {
        flashMessage('update_product', 'Product not found.', FLASH_ERROR);
    }

    // Redirect to the edit page to avoid form resubmission issues
    header('Location: ../admin_update_item.php?id=' . $productId);
    exit;
}



if (get('action') === 'add_stock') {

    $product = Guard::hasModel(Product::class);
    $productId = get('id');
    $productCode = get('product_code');
    $quantity = get('quantity');
    $supplierName = get('supplier');
    $receivedBy = get('received_by'); // New field for "Received By"
    $updatedBy = $_SESSION['user_name'] ?? null; // Use the logged-in user's name

    if (empty($productId) || !is_numeric($quantity) || $quantity <= 0) {
        flashMessage('add_stock', 'Invalid product ID or quantity.', FLASH_ERROR);
        redirect('../admin_home.php');
    }

    if (empty($supplierName)) {
        flashMessage('add_stock', 'Supplier name is required.', FLASH_ERROR);
        redirect('../admin_home.php');
    }

    if (empty($receivedBy)) {
        flashMessage('add_stock', 'Received By field is required.', FLASH_ERROR);
        redirect('../admin_home.php');
    }

    if (empty($updatedBy)) {
        flashMessage('add_stock', 'Session expired. Please log in again.', FLASH_ERROR);
        redirect('../login.php'); // Redirect to login if the session is missing
    }

    try {
        global $connection;

        // Update the product's quantity
        $updateSql = 'UPDATE products SET quantity = quantity + :quantity WHERE id = :id';
        $updateStmt = $connection->prepare($updateSql);
        $updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $updateStmt->execute();

        // Log the inventory update
        $logSql = 'INSERT INTO inventory_log (product_id, quantity_added, supplier_name, received_by, updated_by) 
        VALUES (:product_id, :quantity_added, :supplier_name, :received_by, :updated_by)';
        $logStmt = $connection->prepare($logSql);
        $logStmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $logStmt->bindParam(':quantity_added', $quantity, PDO::PARAM_INT);
        $logStmt->bindParam(':supplier_name', $supplierName, PDO::PARAM_STR);
        $logStmt->bindParam(':received_by', $receivedBy, PDO::PARAM_STR); // New field
        $logStmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_STR);
        $logStmt->execute();

        flashMessage('add_stock', 'Stock updated and logged successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        error_log('Error updating stock: ' . $ex->getMessage());
        flashMessage('add_stock', 'An error occurred.', FLASH_ERROR);
    }

    redirect('../admin_home.php');
}


?>
