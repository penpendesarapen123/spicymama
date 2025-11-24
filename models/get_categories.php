<?php


require_once '../_init.php';

// Ensure this script is only accessed via AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    exit;
}

// Fetch categories
$categories = Category::all();

// Output categories as JSON
echo json_encode($categories);
