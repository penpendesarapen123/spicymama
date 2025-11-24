<?php
require_once 'models/Supplier.php';
require_once '_config.php';
require_once '_helper.php';
require_once 'models/User.php';
require_once 'models/Category.php';
require_once 'models/Product.php';
require_once 'models/Order.php';
require_once 'models/OrderItem.php';
require_once 'models/Sales.php';
require_once 'models/Discount.php';
require_once 'models/Attendance.php';
require_once 'models/CashDrawer.php';
require_once 'models/UserLogs.php';
require_once 'models/return_item.php';
require_once '_guards.php';


session_start();

try {
    $connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_DATABASE, DB_USERNAME, DB_PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-type: text/plain');
    die("Error: Failed to connect to database\nReason: {$e->getMessage()}");
}
?>
