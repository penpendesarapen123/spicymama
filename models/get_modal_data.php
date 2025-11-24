<?php
// Include database connection
require_once __DIR__.'/../_init.php';

// Array to store the final response
$response = [
    'users' => [],
    'products' => []
];

// Fetch user data
$userQuery = "SELECT id, name FROM users";
$userResult = mysqli_query($conn, $userQuery);

if ($userResult && mysqli_num_rows($userResult) > 0) {
    while ($row = mysqli_fetch_assoc($userResult)) {
        $response['users'][] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
}

// Fetch product data
$productQuery = "SELECT code, name, category FROM products";
$productResult = mysqli_query($conn, $productQuery);

if ($productResult && mysqli_num_rows($productResult) > 0) {
    while ($row = mysqli_fetch_assoc($productResult)) {
        $response['products'][] = [
            'code' => $row['code'],
            'name' => $row['name'],
            'category' => $row['category']
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
