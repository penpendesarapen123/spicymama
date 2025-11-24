<?php

require_once '../_init.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Return an error response if the ID is missing or invalid
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}


$product = Product::find($_GET['id']);


if (!$product) {
    
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

$productDetails = [
    'id' => $product->id,
    'name' => $product->name,
    'category' => [
        'name' => $product->category->name
    ],
    'quantity' => $product->quantity,
    'price' => $product->price,
    // Include other properties as needed
];

// Return the product details in JSON format
echo json_encode($productDetails);

?>
