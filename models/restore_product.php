<?php
require_once __DIR__.'/../_init.php';

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);

    try {
        $product = Product::find($productId);

        if ($product) {
            $product->restore();
            header('Location: ../admin_restore.php?restore=success');
            exit;
        } else {
            header('Location: ../admin_restore.php?restore=failure');
            exit;
        }
    } catch (Exception $e) {
        error_log("Error restoring product: " . $e->getMessage());
        header('Location: ../admin_restore.php?restore=error');
        exit;
    }
} else {
    header('Location: ../admin_restore.php?restore=invalid');
    exit;
}
