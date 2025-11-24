<?php
require_once '_guards.php'; // Ensure appropriate access control
require_once 'models/Supplier.php'; // Include the Supplier model

Guard::adminAndManagerOnly(); // Guard to restrict access

// Fetch the supplier data based on the ID from the URL
$supplierId = $_GET['id'] ?? null;
$supplier = Supplier::findById($supplierId);

// Handle form submission for updating supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['supplier_name'];
    $contact = $_POST['phone_number'];
    $address = $_POST['address'];

    // Update the supplier details in the database
    Supplier::update($supplierId, $name, $contact, $address);

    // Redirect to supplier management page after update
    header('Location: admin_suppliers.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Edit Supplier</h2>

        <?php if ($supplier): ?>
            <form action="admin_update_supplier.php?id=<?= htmlspecialchars($supplier->supplier_id) ?>" method="POST">
                <div class="mb-3">
                    <label for="supplier_name" class="form-label">Supplier Name</label>
                    <input type="text" class="form-control" id="supplier_name" name="supplier_name" value="<?= htmlspecialchars($supplier->supplier_name) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($supplier->address) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        <?php else: ?>
            <p>Supplier not found.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
